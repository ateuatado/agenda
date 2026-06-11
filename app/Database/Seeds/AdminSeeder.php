<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\Shield\Entities\User;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        /** @var \CodeIgniter\Shield\Models\UserModel $users */
        $users = auth()->getProvider();

        // Check if admin already exists
        $existing = $users->findByCredentials(['email' => 'marcosantofoto@gmail.com']);
        if ($existing !== null) {
            echo "Admin user already exists, skipping." . PHP_EOL;
            return;
        }

        $user = new User([
            'username' => 'marcosantofoto',
            'active'   => true,
        ]);

        $users->save($user);
        $userId = $users->getInsertID();
        $user   = $users->findById($userId);

        // Set email + password
        $user->createEmailIdentity([
            'email'    => 'marcosantofoto@gmail.com',
            'password' => 'Lula#Eleito26',
        ]);

        // Assign superadmin group
        $user->addGroup('superadmin');

        echo "Admin user 'marcosantofoto' created successfully." . PHP_EOL;
    }
}
