<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Sale;

class DashboardTest extends TestCase
{
    /** @test */
    public function authenticated_user_can_view_dashboard()
    {
        $user = $this->loginUser();

        $response = $this->get('/dashboard');

        $response->assertStatus(200);
        $response->assertViewIs('dashboard.index');
    }

    /** @test */
    public function unauthenticated_user_cannot_view_dashboard()
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect('/login');
    }

    /** @test */
    public function dashboard_displays_metrics()
    {
        $user = $this->loginUser();
        Sale::factory()->count(5)->create();

        $response = $this->get('/dashboard');

        $response->assertViewHas('metrics');
        $response->assertViewHas('topProducts');
        $response->assertViewHas('topCustomers');
    }

    /** @test */
    public function dashboard_displays_chart_data()
    {
        $user = $this->loginUser();

        $response = $this->get('/dashboard');

        $response->assertViewHas('salesTrend');
        $response->assertViewHas('revenueVsExpenses');
        $response->assertViewHas('monthlyComparison');
    }
}