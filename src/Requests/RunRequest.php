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
            'executorParams' => ['string'],
            'executorBlockStrategy' => ['string'],
            'executorTimeout' => ['numeric'],
            'logId' => ['numeric'],
            'logDateTime' => ['numeric'],
            'glueType' => ['string'],
            'glueSource' => ['string'],
            'glueUpdatetime' => ['numeric'],
            'broadcastIndex' => ['numeric'],
            'broadcastTotal' => ['numeric'],
        ];
    }
}
