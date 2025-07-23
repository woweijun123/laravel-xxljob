<?php
namespace XxlJob\Requests;

use Illuminate\Foundation\Http\FormRequest;

class JobIdRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'jobId' => ['required', 'numeric'],
        ];
    }
}
