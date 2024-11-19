<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
	
	Route::get('/download_docs', [ 'as' => 'download_docs', 'uses' => 'App\Http\Controllers\MainController@download_docs']);	
	
	Route::get('/edit_doc', [ 'as' => 'edit_doc', 'uses' => 'App\Http\Controllers\MainController@edit_doc']);    

    Route::get('/make_provv', [ 'as' => 'make_provv', 'uses' => 'App\Http\Controllers\ControllerProvvisori@make_provv']);	    	

	Route::get('elenco_master', [ 'as' => 'elenco_master', 'uses' => 'App\Http\Controllers\ControllerMaster@elenco_master']);
	Route::post('elenco_master', [ 'as' => 'elenco_master', 'uses' => 'App\Http\Controllers\ControllerMaster@elenco_master']);
    
    Route::post('dele_master', [ 'as' => 'dele_master', 'uses' => 'App\Http\Controllers\ControllerMaster@dele_master']);
    Route::post('save_master', [ 'as' => 'save_master', 'uses' => 'App\Http\Controllers\ControllerMaster@save_master']);

    
    Route::get('elenco_provvisori', [ 'as' => 'elenco_provvisori', 'uses' => 'App\Http\Controllers\ControllerProvvisori@elenco_provvisori']);

    Route::get('edit_provvisorio/{id}/{id_provv}', [ 'as' => 'edit_provvisorio', 'uses' => 'App\Http\Controllers\ControllerEditProvvisori@edit_provvisorio']);

    Route::get('all_tag', [ 'as' => 'all_tag', 'uses' => 'App\Http\Controllers\ControllerEditProvvisori@all_tag']);


	Route::get('elenco_lotti', [ 'as' => 'elenco_lotti', 'uses' => 'App\Http\Controllers\ControllerProvvisori@elenco_lotti']);
	Route::post('elenco_lotti', [ 'as' => 'elenco_lotti', 'uses' => 'App\Http\Controllers\ControllerProvvisori@elenco_lotti']);
    Route::post('crea_provv', [ 'as' => 'crea_provv', 'uses' => 'App\Http\Controllers\ControllerProvvisori@crea_provv']);

	Route::get('save_tag_edit', [ 'as' => 'save_tag_edit', 'uses' => 'App\Http\Controllers\ControllerEditProvvisori@save_tag_edit']);
	Route::post('save_tag_edit', [ 'as' => 'save_tag_edit', 'uses' => 'App\Http\Controllers\ControllerEditProvvisori@save_tag_edit']);

	Route::get('view_tag', [ 'as' => 'view_tag', 'uses' => 'App\Http\Controllers\ControllerEditProvvisori@view_tag']);
	Route::post('view_tag', [ 'as' => 'view_tag', 'uses' => 'App\Http\Controllers\ControllerEditProvvisori@view_tag']);

	Route::get('load_clone', [ 'as' => 'load_clone', 'uses' => 'App\Http\Controllers\ControllerEditProvvisori@load_clone']);
	Route::post('load_clone', [ 'as' => 'load_clone', 'uses' => 'App\Http\Controllers\ControllerEditProvvisori@load_clone']);


	Route::get('save_dati', [ 'as' => 'save_dati', 'uses' => 'App\Http\Controllers\ControllerEditProvvisori@save_dati']);
	Route::post('save_dati', [ 'as' => 'save_dati', 'uses' => 'App\Http\Controllers\ControllerEditProvvisori@save_dati']);

	Route::get('save_to_ready', [ 'as' => 'save_to_ready', 'uses' => 'App\Http\Controllers\ControllerEditProvvisori@save_to_ready']);
	Route::post('save_to_ready', [ 'as' => 'save_to_ready', 'uses' => 'App\Http\Controllers\ControllerEditProvvisori@save_to_ready']);


    Route::get('elenco_definitivi_idonei', [ 'as' => 'elenco_definitivi_idonei', 'uses' => 'App\Http\Controllers\ControllerDefinitivi@elenco_definitivi_idonei']);

    Route::get('elenco_definitivi_non_idonei', [ 'as' => 'elenco_definitivi_non_idonei', 'uses' => 'App\Http\Controllers\ControllerDefinitivi@elenco_definitivi_non_idonei']);


    Route::get('new_master', [ 'as' => 'new_master', 'uses' => 'App\Http\Controllers\ControllerMaster@new_master']);
    Route::post('duplica_master', [ 'as' => 'duplica_master', 'uses' => 'App\Http\Controllers\ControllerMaster@duplica_master']);
    
    Route::get('load_rev', [ 'as' => 'load_rev', 'uses' => 'App\Http\Controllers\ControllerMaster@load_rev']);
    Route::post('load_rev', [ 'as' => 'load_rev', 'uses' => 'App\Http\Controllers\ControllerMaster@load_rev']);
    

    Route::post('to_def', [ 'as' => 'to_def', 'uses' => 'App\Http\Controllers\ControllerMaster@to_def']);
    


    //per aggiornare tutti i riferimenti dei master nella tabella locale (tbl_master)
    Route::get('list_update', [ 'as' => 'list_update', 'uses' => 'App\Http\Controllers\MainController@list_update']);

});



require __DIR__.'/auth.php';
