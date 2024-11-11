<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Product;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::create([
            'name' => 'Pemrograman',
            'email' => 'pemrograman11@gmail.com',
            'password' => 'P@ssw0rd'
        ]);

        Admin::create([
            'name' => 'Admin',
            'email' => 'admin@gmail.com',
            'password' => 'P@ssw0rd'
        ]);

        Product::create([
            'title' => 'Temperatur Probe untuk Humidifier F&P MR700/730',
            'price' => 60000,
            'description' => "Lorem ipsum, dolor sit amet consectetur adipisicing elit. Consequuntur, ex."
        ]);
        Product::create([
            'title' => 'Dompet kulit sintetis dompet pria murah dompet lipat pria dompet fashion pria',
            'price' => 15000,
            'description' => "Lorem ipsum dolor sit, amet consectetur adipisicing elit. Aliquam, neque?"
        ]);
        Product::create([
            'title' => 'EASYTOUCH - Strip Test Gula Darah / Asam Urat / Kolesterol Easy Touch - Gula Darah',
            'price' => 92000,
            'description' => "Lorem ipsum dolor sit amet consectetur adipisicing elit. Nostrum, dolore."
        ]);
        Product::create([
            'title' => 'STRIP EASY TOUCH GULA DARAH / STIK EASY TOUCH GULA DARAH',
            'price' => 88000,
            'description' => "Lorem ipsum dolor sit amet consectetur, adipisicing elit. Quidem, beatae."
        ]);
        Product::create([
            'title' => 'STRIP EASY TOUCH',
            'price' => 78000,
            'description' => "Lorem ipsum dolor sit amet consectetur, adipisicing elit. Quidem, beatae."
        ]);
    }
}
