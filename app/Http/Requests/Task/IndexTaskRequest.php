<?php

namespace App\Http\Requests\Task;

use Illuminate\Foundation\Http\FormRequest;

class IndexTaskRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'is_finished' => 'boolean',
            'is_expired' => 'boolean',
            'user_id' => 'int',
            'per_page' => 'int|max:100',
            'page' => 'int',
        ];
    }
}
