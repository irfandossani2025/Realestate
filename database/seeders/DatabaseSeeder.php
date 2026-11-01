<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $companies = [
            [
                'name' => 'Meyar for Valuation',
                'name_ar' => 'ميار للتقييم',
                'slug' => 'meyar',
                'domain' => 'meyarforvaluation.com',
                'business_type' => 'valuation',
                'currency' => 'OMR',
                'is_active' => true,
            ],
            [
                'name' => 'Marsous Marketing',
                'name_ar' => 'مرصوص للتسويق',
                'slug' => 'marsous',
                'domain' => 'marsousrealestate.com',
                'business_type' => 'marketing',
                'currency' => 'OMR',
                'is_active' => true,
            ],
            [
                'name' => 'Al Ramz Real Estate',
                'name_ar' => 'الرمز للعقارات',
                'slug' => 'al-ramz',
                'domain' => 'alramzinternational.com',
                'business_type' => 'sales_rentals',
                'currency' => 'OMR',
                'is_active' => true,
            ],
        ];

        foreach ($companies as $company) {
            Company::query()->updateOrCreate(['slug' => $company['slug']], $company);
        }
    }
}
