<?php

use App\Http\Controllers\YandexController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ProfileFeedbackController;


Route::get('information', ['as' => 'admin.information', function () {
	$content = 'Define your information here.';
	return AdminSection::view($content, 'Information');
}]);

Route::get('admin/get-statistics', [ReportController::class, 'index'])->name('admin.get-statistics');

Route::get('admin/get-report/{id}', [ReportController::class, 'getReport'])->name('admin.get-report');

Route::get('get-feedbacks', [ProfileFeedbackController::class, 'index'])->name('admin.get-feedback');

Route::post('get-feedbacks', [ProfileFeedbackController::class, 'getFeedbacksCount']);