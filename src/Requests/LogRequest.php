<?php

namespace XxlJob\Requests;
use Illuminate\Foundation\Http\FormRequest;

class LogRequest  extends FormRequest
{
    public function rules(): array
    {
        return [
            'logId' => ['required', 'numeric'],
            'logDateTim' => ['numeric'],
            'fromLineNum' => ['numeric'],
        ];
    }
}
