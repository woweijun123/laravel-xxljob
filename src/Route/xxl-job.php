<?php
use XxlJob\Controller\XxlJobController;

Route::prefix('xxljob')->group(function () {
    Route::post('/beat', [XxlJobController::class, 'beat']);
    Route::post('/idleBeat', [XxlJobController::class, 'idleBeat']);
    Route::post('/run', [XxlJobController::class, 'run']);
    Route::post('/kill', [XxlJobController::class, 'kill']);
    Route::post('/log', [XxlJobController::class, 'log']);
});
