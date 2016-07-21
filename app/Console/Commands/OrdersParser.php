<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Order;
use SoapBox\Formatter\Formatter;


class OrdersParser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:parser';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Parsing orders';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public  static function arrayFind($array, $matching) {
        $return = [];
        foreach ($array as $item) {
            $is_match = true;
            foreach ($matching as $key => $value) {
                if (is_object($item)) {
                    if (! isset($item->$key)) {
                        $is_match = false;
                        break;
                    }
                } else {
                    if (! isset($item[$key])) {
                        $is_match = false;
                        break;
                    }
                }
                if (is_object($item)) {
                    if ($item->$key != $value) {
                        $is_match = false;
                        break;
                    }
                } else {
                    if ($item[$key] != $value) {
                        $is_match = false;
                        break;
                    }
                }
            }
            if ($is_match) {
                $return[] = $item;
            }
        }
        return $return;
    }

    public static function arrayFormatter($array, $keys, $format)
    {
        $res = [];
        $additional = [];
        $static = config('orders.static_fields.'.$format);
        foreach($array as $v1) {
            $t1 = [];
            $t2 = [];
            foreach($v1 as $k2 => $v2) {
                if(in_array($k2, $keys)) {
                    $t1[$k2] = $v2;
                } else {
                    $t2[$k2] = $v2;
                }
            }
            $res[] = array_merge($t1, $static);
            $additional[] = $t2;
        }
        $real_keys = config('orders.fields_names.'.$format);
        //dd($res);
        $res = array_map(function($res) use ($real_keys) {
            return [
                'action_date' => $res[$real_keys['action_date']],
                'order_id' => $res[$real_keys['order_id']],
                'cart' => $res[$real_keys['cart']],
                'currency' => $res[$real_keys['currency']],
                'status' => $res[$real_keys['status']],
                'advcampaign_id' => $res[$real_keys['advcampaign_id']],
            ];
        }, $res);
        foreach ($res as $k => $v) {
            $res[$k]['additional'] = json_encode($additional[$k]);
        }
        return($res);
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        function microtime_float()
        {
            list($usec, $sec) = explode(" ", microtime());
            return ((float)$usec + (float)$sec);
        }
        $resources = [
            ['location' => public_path().'/files/xml.xml', 'format' => 'xml'],
            ['location' => public_path().'/files/csv.csv', 'format' => 'csv'],
        ];
        $created = 0;
        $updated = 0;
        $time_start = microtime_float();
        foreach ($resources as $resource) {
            $content = \File::get($resource['location']);
            $format = $resource['format'];
            $formatter = Formatter::make($content, $format);
            $wrapper = config('orders.wrapper.'.$format);
            if($wrapper === false) {
                $orders_arr = $formatter->toArray();
            } else {
                $orders_arr = $formatter->toArray()[$wrapper];
            }
            $additional_filter = config('orders.additional_filter.'.$format);
            if($additional_filter !== false) {
                $orders_arr = OrdersParser::arrayFind($orders_arr, $additional_filter);
            }
            $columns = config('orders.columns.'.$format);
            $orders_data = OrdersParser::arrayFormatter($orders_arr, $columns, $format);
            $check_ord = [];
            foreach($orders_data as $key => $val) {
                $check_ord[$key]['advcampaign_id'] = $val['advcampaign_id'];
                $check_ord[$key]['order_id'] = $val['order_id'];
                $check_ord[$key]['cart'] = $val['cart'];
                $check_ord[$key]['status'] = $val['status'];
                $check_ord[$key]['currency'] = $val['currency'];
                $check_ord[$key]['action_date'] = $val['action_date'];
                $check_ord[$key]['additional'] = $val['additional'];
            }
            $exists_ord = Order::get()->toArray();
            $to_insert = array_filter($check_ord, function ($array2Element) use ($exists_ord) {
                foreach ($exists_ord as $array1Element) {
                    if ($array1Element['advcampaign_id'] == $array2Element['advcampaign_id']
                        && $array1Element['order_id'] == $array2Element['order_id']) {
                        return false;
                    }
                }
                return true;
            });
            Order::insert($to_insert);
            $created += count($to_insert);
            $to_update = array_filter($check_ord, function ($array2Element) use ($exists_ord) {
                foreach ($exists_ord as $array1Element) {
                    if ($array1Element['advcampaign_id'] == $array2Element['advcampaign_id']
                        && $array1Element['order_id'] == $array2Element['order_id']) {
                        if($array1Element['status'] != $array2Element['status'] || $array1Element['currency'] != $array2Element['currency']
                            || $array1Element['cart'] != $array2Element['cart'] || $array1Element['action_date'] != $array2Element['action_date']) {
                            return true;
                        } else
                            return false;
                    }
                }
                return false;
            });
            $to_update = array_filter($to_update, function($arr2) use($to_update) {
                foreach($to_update as $upd) {
                    if($upd['order_id'] == $arr2['order_id'] && $upd['advcampaign_id'] == $arr2['advcampaign_id'])
                        return false;
                }
                return true;
            });
            $updated += count($to_update);
            Order::updateOrders($to_update);
        }
        $time_end = microtime_float();
        $time = $time_end - $time_start;
        $this->info('DONE. created: '.$created.'; updated: '.$updated.'. After '.$time.'sec.');
    }
}
