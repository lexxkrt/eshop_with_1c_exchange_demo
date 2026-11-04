<?php

namespace App\Services\OneC;

use App\Models\Order;
use Illuminate\Support\Carbon;

/**
 * Экспорт заказов в CommerceML 2 (type=sale) для загрузки в 1С.
 */
class OrdersExporter
{
    /**
     * Выгружает заказы в XML CommerceML.
     *
     * @param  iterable<int, Order>|null  $orders  Если не задано — все невыгруженные заказы.
     */
    public function export(?iterable $orders = null): string
    {
        $orders ??= Order::with('items')->whereNull('external_id')->orderBy('id')->get();

        $xml = new \XMLWriter();
        $xml->openMemory();
        $xml->setIndent(true);
        $xml->startDocument('1.0', 'UTF-8');
        $xml->startElement('КоммерческаяИнформация');
        $xml->writeAttribute('ВерсияСхемы', '2.07');
        $xml->writeAttribute('ДатаФормирования', Carbon::now()->format('Y-m-d\TH:i:s'));

        foreach ($orders as $order) {
            $this->writeOrder($xml, $order);
        }

        $xml->endElement();

        return $xml->outputMemory();
    }

    /**
     * Помечает заказы выгруженными (mode=success со стороны 1С).
     *
     * @param  array<int, int>  $orderIds
     */
    public function markExported(array $orderIds): int
    {
        return Order::whereIn('id', $orderIds)
            ->whereNull('external_id')
            ->update(['exported_at' => Carbon::now()]);
    }

    private function writeOrder(\XMLWriter $xml, Order $order): void
    {
        $xml->startElement('Документ');
        $xml->writeElement('Ид', (string) $order->id);
        $xml->writeElement('Номер', $this->orderNumber($order));
        $xml->writeElement('Дата', $order->created_at->format('Y-m-d'));
        $xml->writeElement('Время', $order->created_at->format('H:i:s'));
        $xml->writeElement('ХозОперация', 'Заказ товара');
        $xml->writeElement('Роль', 'Продавец');
        $xml->writeElement('Валюта', 'RUB');
        $xml->writeElement('Курс', '1');
        $xml->writeElement('СуммаДокумента', number_format((float) $order->total, 2, '.', ''));

        // Контрагент — покупатель.
        $xml->startElement('Контрагенты');
        $xml->startElement('Контрагент');
        $xml->writeElement('Ид', 'user-' . $order->customer_email);
        $xml->writeElement('Наименование', $order->customer_name);
        $xml->startElement('Контакты');
        $xml->startElement('Контакт');
        $xml->writeElement('Тип', 'Почта');
        $xml->writeElement('Значение', $order->customer_email);
        $xml->endElement();
        $xml->startElement('Контакт');
        $xml->writeElement('Тип', 'Телефон');
        $xml->writeElement('Значение', $order->customer_phone);
        $xml->endElement();
        $xml->endElement();
        $xml->endElement();
        $xml->endElement();

        // Товары.
        $xml->startElement('Товары');

        foreach ($order->items as $item) {
            $xml->startElement('Товар');

            if ($item->product?->external_id) {
                $xml->writeElement('Ид', $item->product->external_id);
            } else {
                $xml->writeElement('Ид', 'order-item-' . $item->id);
            }

            $xml->writeElement('Наименование', $item->product_name);
            $xml->writeElement('ЦенаЗаЕдиницу', number_format((float) $item->price, 2, '.', ''));
            $xml->writeElement('Количество', (string) $item->quantity);
            $xml->writeElement('Сумма', number_format($item->subtotal(), 2, '.', ''));
            $xml->endElement();
        }

        $xml->endElement();

        $xml->endElement();
    }

    private function orderNumber(Order $order): string
    {
        return 'ORDER-' . str_pad((string) $order->id, 6, '0', STR_PAD_LEFT);
    }
}
