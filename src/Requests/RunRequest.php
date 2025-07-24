<?php
namespace XxlJob\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RunRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'jobId' => ['required', 'numeric'],
            'executorHandler' => ['required', 'string'],
            'executorParams' => ['nullable', 'string'],
            'executorBlockStrategy' => ['string'],
            'executorTimeout' => ['numeric'],
            'logId' => ['numeric'],
            'logDateTime' => ['numeric'],
            'glueType' => ['string'],
            'glueSource' => ['nullable', 'string'],
            'glueUpdatetime' => ['numeric'],
            'broadcastIndex' => ['numeric'],
            'broadcastTotal' => ['numeric'],
        ];
    }
}
