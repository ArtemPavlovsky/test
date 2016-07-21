<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{

    public $timestamps = false;

    protected $fillable = ['advcampaign_id', 'order_id', 'status', 'cart', 'currency', 'action_date', 'additional'];

    public static function updateOrders($to_update)
    {
        foreach($to_update as $i => $j) {
            $order = Order::where('advcampaign_id', $j['advcampaign_id'])->where('order_id', $j['order_id'])->first();
            $order->cart = $j['cart'];
            $order->status = $j['status'];
            $order->currency = $j['currency'];
            $order->action_date = $j['action_date'];
            $order->action_date = $j['action_date'];
            $order->additional = $j['additional'];
            $order->update();
        }
    }

}
