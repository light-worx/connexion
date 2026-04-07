<?php

use App\Http\Middleware\CheckLogin;
use Illuminate\Support\Facades\Route;
use Spatie\Honeypot\ProtectAgainstSpam;
use App\Http\Controllers\AppController;
use Livewire\Livewire;

/*Livewire::setUpdateRoute(function ($handle) {
    return Route::post('/custom/livewire/update', $handle)->middleware(['web']);
});*/
// App routes
Route::domain(env('PWA_DOMAIN'))->group(function() {
    //Route::middleware(['web',CheckLogin::class])->controller('\App\Http\Controllers\HomeController')->group(function () {
    Route::middleware(['web'])->controller(AppController::class)->group(function () {
        Route::get('/', 'app')->name('app.home');
        Route::get('/blog/{year}/{month}/{slug}', 'blogpost')->name('app.blogpost');
        Route::get('/blog', 'blog')->name('app.blog');
        Route::get('/blog/{slug}', 'blogger')->name('app.blogger');
        Route::get('/books/{id}', 'book')->name('app.book');
        Route::get('/books', 'books')->name('app.books');
        Route::get('/calendar/{full?}', 'calendar')->name('app.calendar');
        Route::get('/contact', 'contact')->name('app.contact');
        Route::get('/courses', 'courses')->name('app.courses');
        Route::get('/courses/{id}', 'course')->name('app.course');
        Route::get('/courses/{id}/{session}', 'session')->name('app.session');
        Route::get('/details', 'details')->name('app.details');
        Route::get('/devotionals', 'devotionals')->name('app.devotionals');
        Route::get('/events', 'events')->name('app.events');
        Route::get('/events/{id}', 'event')->name('app.event');
        Route::get('/find', 'find')->name('app.directory');
        Route::get('/groups', 'groups')->name('app.groups');
        Route::get('/groups/{id}', 'group')->name('app.group');
        Route::get('/login', 'login')->name('app.login');
        Route::get('/offline', 'offline')->name('app.offline');
        Route::get('/paths/{url}', 'path')->name('app.path');
        Route::get('/practices', 'practices')->name('app.practices');
        Route::get('/people/{slug}', 'person')->name('app.person');
        Route::get('/projects/{slug}', 'project')->name('app.project');
        Route::get('/projects', 'projects')->name('app.projects');
        Route::get('/pastoral', 'pastoral')->name('app.pastoral');
        Route::get('/pastoral/{type}/{id}', 'pastoralcase')->name('app.pastoralcase');
        Route::get('/preacher/{slug}', 'preacher')->name('app.preacher');
        Route::get('/rosterdates', 'rosterdates')->name('app.rosterdates');
        Route::get('/sermons', 'sermons')->name('app.sermons');
        Route::get('/sermons/{year}/{slug}', 'series')->name('app.series');
        Route::get('/sermon/{year}/{slug}/{id}', 'sermon')->name('app.sermon');
        Route::get('/service/{id}', 'service')->name('app.service');
        Route::get('/settings', 'settings')->name('app.settings');
        Route::get('/songs', 'songs')->name('app.songs');
        Route::get('/songs/{id}', 'song')->name('app.song');
        Route::get('/subject/{slug}', 'subject')->name('app.subject');
        Route::get('/teams/{id}', 'team')->name('app.team');
        Route::get('/teams', 'teams')->name('app.teams');
        Route::get('/worship', 'worship')->name('app.worship');
        if (substr(str_replace(env('APP_URL'),'',url()->current()),1)<>"admin"){
            Route::get('/{page}', 'page')->name('app.page');
        }     
    });
});

// Website routes
Route::middleware(['web'])->controller('\App\Http\Controllers\WebController')->group(function () {
    Route::get('/', 'home')->name('web.home');
    Route::post('/', 'home')->middleware(ProtectAgainstSpam::class)->name('web.home');
    Route::get('/blog/{year}/{month}/{slug}', 'blogpost')->name('web.blogpost');
    Route::get('/blog', 'blog')->name('web.blog');
    Route::get('/blog/{slug}', 'blogger')->name('web.blogger');
    Route::get('/contact', 'contact')->name('web.contact');
    Route::get('/courses', 'courses')->name('web.courses');
    Route::get('/courses/{id}', 'course')->name('web.course');      
    Route::get('/courses/{id}/{session}', 'session')->name('web.session');
    Route::get('/events', 'events')->name('web.events');
    Route::get('/events/{id}', 'event')->name('web.event');        
    Route::get('/groups', 'groups')->name('web.groups');
    Route::get('/groups/{id}', 'group')->name('web.group');
    Route::get('/offline', 'offline')->name('web.offline');
    Route::get('/people/{slug}', 'person')->name('web.person');
    Route::get('/preacher/{slug}', 'preacher')->name('web.preacher');
    Route::get('/projects/{slug}', 'project')->name('web.project');
    Route::get('/projects', 'projects')->name('web.projects');
    Route::get('/quietmoments', 'quietmoments')->name('web.quietmoments');
    Route::get('/rosters/{slug}', 'roster')->name('web.roster');
    Route::get('/sermons', 'sermons')->name('web.sermons');
    Route::get('/sermons/{year}/{slug}', 'series')->name('web.series');
    Route::get('/sermon/{year}/{slug}/{id}', 'sermon')->name('web.sermon');
    Route::get('/stayingconnected', 'stayingconnected')->name('web.stayingconnected');
    Route::get('/subject/{slug}', 'subject')->name('web.subject');
    Route::get('/sundaydetails', 'sunday')->name('web.sunday');
    if (substr(url()->current(), strrpos(url()->current(), '/' )+1)<>"admin"){
        Route::get('/{page}', 'page')->name('web.page');
    }
});


