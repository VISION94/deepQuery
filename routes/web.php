<?php

use App\Models\funding_investors;
use App\Models\Investor;
use App\Models\Startup;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Overtrue\LaravelLike\Like;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    $user = User::find(1);
    $likes = [];
    try {
        $likes = json_decode($user->likes);
    } catch (Exception $ex) {
        $likes = [];
    }

    $unwindActivity = [];
    foreach ($likes as $like) {
        if ($like->likeable_type == 'App\Models\Startup') {
            Startup::where('user_id', $like->likeable_id)->chunkById(Startup::count(), function ($startupDetails) use (&$unwindActivity) {
                foreach ($startupDetails as $startup) {
                    $startup['type'] = 'Startup';
                    foreach (json_decode($startup->News) as $news) {
                        $news->type = 'news';
                        $news->date = date('Y-m-d', strtotime($news->date_published));
                        $news->timestamp = $news->date_published;
                        array_push($unwindActivity, array_merge([json_decode($startup, true)][0], ['activity' => $news]));
                    }
                    foreach (json_decode($startup->Funding) as $funding) {
                        $funding->type = 'funding';
                        $funding->date = date('Y-m-d', strtotime($funding->date_announced));
                        $funding->timestamp = $funding->date_announced;
                        $funding->Investor = funding_investors::join('investors', 'investors.id', '=', 'funding_investors.investor_id')
                            ->where('funding_id', $funding->id)
                            ->get();
                        array_push($unwindActivity, array_merge([json_decode($startup, true)][0], ['activity' => $funding]));
                    }
                    foreach (json_decode($startup->Milestones) as $milestone) {
                        $milestone->type = 'milestone';
                        $milestone->date = date('Y-m-d', strtotime($milestone->date_published));
                        $milestone->timestamp = $milestone->date_published;
                        array_push($unwindActivity, array_merge([json_decode($startup, true)][0], ['activity' => $milestone]));
                    }
                }
            });
        } elseif ($like->likeable_type == 'App\Models\Investor') {
            Investor::where('id', $like->likeable_id)->chunkById(Investor::count(), function ($investors) use (&$unwindActivity) {
                foreach ($investors as $investor) {
                    $investor['type'] = 'Investor';
                    foreach (json_decode($investor->News) as $news) {
                        $news->type = 'news';
                        $news->date = date('Y-m-d', strtotime($news->date_published));
                        $news->timestamp = $news->date_published;
                        array_push($unwindActivity, array_merge([json_decode($investor, true)][0], ['activity' => $news]));
                    }
                }
            });
        }
    }

    $uniques = $startupActivity = [];
    foreach ($unwindActivity as $c) {
        $uniques[$c['activity']->date] = $c;
    }
    $uniques = collect($uniques)->sortBy('activity.timestamp')->reverse()->toArray();

    foreach ($uniques as $date => $value) {
        $count = 0;

        foreach (array_reverse($likes) as $like) {
            if ($like->likeable_type == 'App\Models\Startup') {
                Startup::where('user_id', $like->likeable_id)->chunkById(Startup::count(), function ($startupDetails) use (&$unwindActivity, &$date, &$count, &$startupActivity) {
                    foreach ($startupDetails as $startup) {
                        $todayActivity = [];
                        foreach ($unwindActivity as $activity) {
                            $id = 0;
                            try {
                                $id = $activity['activity']->id;
                            } catch (Exception $ex) {
                                $id = $activity['activity']->startup_id;
                            }
                            if ($date == date('Y-m-d', strtotime($activity['activity']->date)) && $startup->id == $id && $activity['type'] == 'Startup') {
                                array_push($todayActivity, $activity['activity']);
                                unset($activity);
                            }
                        }
                        if (count($todayActivity) != 0) {
                            $startup['type'] = 'Startup';
                            array_push($startupActivity, array_merge(['feed' => $startup], ['activity' => $todayActivity]));
                        }
                        $count = $count + 1;
                    }
                });
            } elseif ($like->likeable_type == 'App\Models\Investor') {
                Investor::where('id', $like->likeable_id)->chunkById(Investor::count(), function ($investors) use (&$unwindActivity, &$date, &$count, &$startupActivity, &$user) {
                    foreach ($investors as $investor) {
                        $todayActivity = [];
                        foreach ($unwindActivity as $activity) {
                            $id = 0;
                            try {
                                $id = $activity['activity']->id;
                            } catch (Exception $ex) {
                                $id = $activity['activity']->startup_id;
                            }
                            if ($date == date('Y-m-d', strtotime($activity['activity']->date)) && $investor->id == $id && $activity['type'] == 'Investor') {
                                array_push($todayActivity, $activity['activity']);
                                unset($activity);
                            }
                        }
                        if (count($todayActivity) != 0) {
                            $investor['type'] = 'Investor';
                            $likedata = Like::where('user_id', $user->id)->orderBy('created_at', 'desc')->limit(1)->get();
                            if ($likedata[0]['likeable_type'] == 'App\Models\Investor' && $likedata[0]['likeable_id'] == $investor->id) {
                                array_unshift($startupActivity, array_merge(['feed' => $investor], ['activity' => $todayActivity]));
                            } else {
                                array_push($startupActivity, array_merge(['feed' => $investor], ['activity' => $todayActivity]));
                            }
                        }
                        $count = $count + 1;
                    }
                });
            }
        }
    }

    echo json_encode($startupActivity);

    return view('welcome');
});

Route::get('/like', function () {
    $user = User::find(1);
    $startup = Startup::find(1);
    $investor = Investor::find(1);

    $user->like($startup);
    $user->like($investor);
});
