<?php

namespace App\Validation;

class AuthRules
{
    /**
     * Memastikan domain email termasuk daftar domain yang diizinkan
     * pada pengaturan (Auth.restrictEmailDomain & Auth.allowedEmailDomains).
     */
    public function allowed_email_domain($str, ?string $fields, array $data, ?string &$error = null): bool
    {
        if (! setting('Auth.restrictEmailDomain')) {
            return true;
        }

        $domains = self::allowedDomains();

        if ($domains === []) {
            return true;
        }

        if (! is_string($str) || ! str_contains($str, '@')) {
            return false;
        }

        $domain = strtolower(substr(strrchr($str, '@'), 1));

        if (! in_array($domain, $domains, true)) {
            $error = lang('Validation.allowed_email_domain', [implode(', ', $domains)]);

            return false;
        }

        return true;
    }

    /**
     * @return list<string>
     */
    public static function allowedDomains(): array
    {
        $raw = (string) (setting('Auth.allowedEmailDomains') ?? '');

        $domains = preg_split('/[\s,;]+/', strtolower($raw), -1, PREG_SPLIT_NO_EMPTY);

        return array_values(array_unique(array_map(
            static fn (string $domain): string => ltrim(trim($domain), '@'),
            $domains ?: []
        )));
    }
}
