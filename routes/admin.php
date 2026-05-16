<?php

use App\Http\Controllers\Admin\Inventariocontroller;
use Illuminate\Support\Facades\Route;

Route::resource('inventario', Inventariocontroller::class);
