<?php

namespace App\Notifications\Channels;

use Closure;

final readonly class ChannelField
{
    /**
     * @param  string  $input  HTML input type
     * @param  list<string|Closure>  $rules  validation rules, besides required/nullable
     * @param  bool  $secret  never sent back to the browser; left empty on edit to keep the stored value
     */
    public function __construct(
        public string $label,
        public string $input = 'text',
        public bool $required = true,
        public array $rules = [],
        public string $note = '',
        public bool $secret = false,
        public string $default = '',
    ) {}
}
