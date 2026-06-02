<?php

namespace Tests\Unit;

use App\Services\CostCalculator;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CostCalculatorTest extends TestCase
{
    private CostCalculator $calculator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calculator = new CostCalculator();
    }

    #[Test]
    public function it_calculates_base_monthly_cost_for_rtx_4090(): void
    {
        $cost = $this->calculator->calculateMonthlyBaseCost('RTX_4090', 50);

        $expectedCompute = 0.79 * 730;
        $expectedStorage = 50 * 0.10;

        $this->assertEquals(round($expectedCompute + $expectedStorage, 2), $cost);
    }

    #[Test]
    public function it_calculates_subscription_price_with_default_margin(): void
    {
        $result = $this->calculator->calculateSubscriptionPrice('RTX_4090');

        $expectedBase = round((0.79 * 730) + (50 * 0.10), 2);
        $expectedPrice = round(($expectedBase * 1.35) + 9.99, 2);

        $this->assertEquals('RTX_4090', $result['gpu_tier']);
        $this->assertEquals($expectedBase, $result['base_monthly_cost']);
        $this->assertEquals(0.35, $result['benefit_margin_rate']);
        $this->assertEquals(9.99, $result['fixed_platform_markup']);
        $this->assertEquals($expectedPrice, $result['monthly_subscription_price']);
    }

    #[Test]
    public function it_calculates_subscription_price_with_custom_margin(): void
    {
        $result = $this->calculator->calculateSubscriptionPrice('RTX_4090', 0.50, 15.00);

        $expectedBase = round((0.79 * 730) + (50 * 0.10), 2);
        $expectedPrice = round(($expectedBase * 1.50) + 15.00, 2);

        $this->assertEquals($expectedPrice, $result['monthly_subscription_price']);
        $this->assertEquals(0.50, $result['benefit_margin_rate']);
        $this->assertEquals(15.00, $result['fixed_platform_markup']);
    }

    #[Test]
    public function it_calculates_profit(): void
    {
        $profit = $this->calculator->calculateProfit(500.00, 850.00);
        $this->assertEquals(350.00, $profit);
    }

    #[Test]
    public function it_throws_exception_for_unknown_gpu_tier(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->calculator->calculateMonthlyBaseCost('UNKNOWN_GPU');
    }

    #[Test]
    public function it_calculates_all_gpu_tiers_without_error(): void
    {
        $tiers = ['RTX_4090', 'RTX_A6000', 'A100_40GB', 'A100_80GB', 'H100'];

        foreach ($tiers as $tier) {
            $result = $this->calculator->calculateSubscriptionPrice($tier);
            $this->assertArrayHasKey('monthly_subscription_price', $result);
            $this->assertGreaterThan(0, $result['monthly_subscription_price']);
        }
    }
}
