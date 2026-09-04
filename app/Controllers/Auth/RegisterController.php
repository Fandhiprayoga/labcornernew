<?php

namespace App\Controllers\Auth;

use CodeIgniter\Shield\Controllers\RegisterController as ShieldRegisterController;

class RegisterController extends ShieldRegisterController
{
    /**
     * Tambahkan pembatasan domain email pada aturan registrasi.
     */
    protected function getValidationRules(): array
    {
        $rules = parent::getValidationRules();

        if (isset($rules['email'])) {
            $emailRules = $rules['email']['rules'] ?? $rules['email'];

            if (is_string($emailRules)) {
                $emailRules = explode('|', $emailRules);
            }

            $emailRules[] = 'allowed_email_domain';

            if (isset($rules['email']['rules'])) {
                $rules['email']['rules'] = $emailRules;
            } else {
                $rules['email'] = $emailRules;
            }
        }

        return $rules;
    }
}
