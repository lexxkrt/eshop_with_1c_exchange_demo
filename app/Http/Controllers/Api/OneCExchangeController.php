<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\OneC\CommerceMLImporter;
use App\Services\OneC\OrdersExporter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

/**
 * Модуль обмена с 1С по протоколу CommerceML 2.
 *
 * Endpoint: /api/1c/exchange
 *
 * Каталог (type=catalog):
 *  - mode=checkauth — проверка авторизации;
 *  - mode=init      — параметры обмена (zip, file_limit);
 *  - mode=file      — приём файла обмена;
 *  - mode=import    — разбор файла и обновление данных сайта.
 *
 * Заказы (type=sale):
 *  - mode=checkauth / mode=init    — как для каталога;
 *  - mode=orders (GET)             — выгрузка заказов в CommerceML;
 *  - mode=success (POST, ids=1,2)  — подтверждение загрузки заказов в 1С.
 */
class OneCExchangeController extends Controller
{
    public function __invoke(Request $request): Response
    {
        if (! $this->authenticated($request)) {
            return $this->plain('failure' . PHP_EOL . 'Ошибка авторизации');
        }

        return match ($request->query('mode')) {
            'checkauth' => $this->checkauth(),
            'init' => $this->init(),
            'file' => $this->receiveFile($request),
            'import' => $this->import($request),
            'orders' => $this->orders(),
            'success' => $this->success($request),
            default => $this->plain('failure' . PHP_EOL . 'Неизвестный режим: ' . $request->query('mode')),
        };
    }

    private function authenticated(Request $request): bool
    {
        $login = $request->getUser() ?? $request->query('login');
        $password = $request->getPassword() ?? $request->query('password');

        return hash_equals((string) config('onec.login'), (string) $login)
            && hash_equals((string) config('onec.password'), (string) $password);
    }

    private function checkauth(): Response
    {
        return $this->plain('success' . PHP_EOL . config('session.cookie') . PHP_EOL . session()->getId());
    }

    private function init(): Response
    {
        return $this->plain(sprintf(
            'zip=%s' . PHP_EOL . 'file_limit=%d' . PHP_EOL,
            config('onec.zip') ? 'yes' : 'no',
            config('onec.file_limit')
        ));
    }

    private function receiveFile(Request $request): Response
    {
        $filename = $request->query('filename');

        if (! $filename || ! preg_match('/^[\w.\-]+$/', $filename)) {
            return $this->plain('failure' . PHP_EOL . 'Некорректное имя файла');
        }

        $content = $request->getContent();

        if ($content === '') {
            return $this->plain('failure' . PHP_EOL . 'Пустой файл');
        }

        if (strlen($content) > (int) config('onec.file_limit')) {
            return $this->plain('failure' . PHP_EOL . 'Превышен лимит размера файла');
        }

        Storage::disk('local')->put(config('onec.storage_path') . '/' . $filename, $content);

        return $this->plain('success' . PHP_EOL);
    }

    private function import(Request $request): Response
    {
        $filename = $request->query('filename');

        if (! $filename || ! preg_match('/^[\w.\-]+$/', $filename)) {
            return $this->plain('failure' . PHP_EOL . 'Некорректное имя файла');
        }

        $path = config('onec.storage_path') . '/' . $filename;

        if (! Storage::disk('local')->exists($path)) {
            return $this->plain('failure' . PHP_EOL . "Файл не найден: {$filename}");
        }

        try {
            $importer = new CommerceMLImporter();
            $importer->importFile(Storage::disk('local')->path($path));
        } catch (\Throwable $e) {
            report($e);

            return $this->plain('failure' . PHP_EOL . $e->getMessage());
        }

        return $this->plain('success' . PHP_EOL . $importer->stats() . PHP_EOL);
    }

    private function orders(): Response
    {
        $xml = (new OrdersExporter())->export();

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    private function success(Request $request): Response
    {
        $ids = collect(explode(',', (string) $request->input('ids', '')))
            ->map(fn ($id) => (int) trim($id))
            ->filter()
            ->all();

        $count = (new OrdersExporter())->markExported($ids);

        return $this->plain("success" . PHP_EOL . "exported: {$count}" . PHP_EOL);
    }

    private function plain(string $content): Response
    {
        return response($content, 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
