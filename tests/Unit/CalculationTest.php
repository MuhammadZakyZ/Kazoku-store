<?php

namespace Tests\Unit;

use App\Models\Order;
use App\Models\Setting;
use Tests\ShopTestCase;

class CalculationTest extends ShopTestCase
{
    public function test_unit_price_adds_size_and_all_selected_areas(): void
    {
        $shop = $this->shop();

        $price = Order::unitPriceFor($shop['product'], $shop['xl'], [$shop['chest'], $shop['back']]);

        $this->assertSame(100000 + 5000 + 20000 + 35000, $price);
    }

    public function test_unit_price_without_size_or_areas_is_base_price(): void
    {
        $shop = $this->shop();

        $this->assertSame(100000, Order::unitPriceFor($shop['product'], null, []));
    }

    public function test_dp_is_percentage_of_total_rounded_to_whole_rupiah(): void
    {
        Setting::put('dp_percent', '33');

        $this->assertSame(33, Order::dpAmountFor(101));          // 33,33 -> 33
        $this->assertSame(354750, Order::dpAmountFor(1075000));  // tepat
    }

    public function test_order_code_has_expected_format_and_is_unique(): void
    {
        $codes = [];
        for ($i = 0; $i < 50; $i++) {
            $code = Order::generateCode();
            $this->assertMatchesRegularExpression('/^KZK-\d{6}-[A-HJ-NP-Z2-9]{4}$/', $code);
            $codes[$code] = true;
        }

        $this->assertGreaterThan(40, count($codes)); // hampir semuanya berbeda
    }
}
