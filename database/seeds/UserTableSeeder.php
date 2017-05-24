<?php

use Illuminate\Database\Seeder;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            'name' => 'Lucas Henrique',
            'email' => 'lucas@ae.studio',
            'password' => app('hash')->make('123456'),
            'remember_token' => str_random(10),
        ]);

        DB::table('users')->insert([
            'name' => 'Devin Smith',
            'email' => 'devin@ae.studio',
            'password' => app('hash')->make('123456'),
            'remember_token' => str_random(10),
        ]);

        DB::table('users')->insert([
            'name' => 'Edward Chen',
            'email' => 'edward@ae.studio',
            'password' => app('hash')->make('123456'),
            'remember_token' => str_random(10),
        ]);

        DB::table('users')->insert([
            'name' => 'James Raj',
            'email' => 'james@ae.studio',
            'password' => app('hash')->make('123456'),
            'remember_token' => str_random(10),
        ]);

        DB::table('users')->insert([
            'name' => 'Keith Matthews',
            'email' => 'keith@alice.caltech.edu',
            'password' => app('hash')->make('123456'),
            'remember_token' => str_random(10),
        ]);

        DB::table('users')->insert([
            'name' => 'Emily Boucher',
            'email' => 'emily@publisherdesk.com',
            'password' => app('hash')->make('123456'),
            'remember_token' => str_random(10),
        ]);

        DB::table('users')->insert([
            'name' => 'Daniel Pereira',
            'email' => 'daniel@ae.studio',
            'password' => app('hash')->make('123456'),
            'remember_token' => str_random(10),
        ]);

        DB::table('users')->insert([
            'name' => 'Administrator',
            'email' => 'admin@publisherdesk.com',
            'password' => app('hash')->make('123456'),
            'remember_token' => str_random(10),
        ]);
    }
}
