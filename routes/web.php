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
                    $s = [];
                    $s = json_decode(json_encode($startup), true);
                    $s['type'] = 'App\Models\Startup';
                    foreach (json_decode($startup->News) as $news) {
                        $news->type = 'news';
                        $news->date = date('Y-m-d', strtotime($news->date_published));
                        $news->timestamp = $news->date_published;
                        $s['activity'] = json_decode(json_encode($news), true);
                        array_push($unwindActivity, $s);
                    }
                    foreach (json_decode($startup->Funding) as $funding) {
                        $funding->type = 'funding';
                        $funding->date = date('Y-m-d', strtotime($funding->date_announced));
                        $funding->timestamp = $funding->date_announced;
                        $funding->Investor = funding_investors::join('investors', 'investors.id', '=', 'funding_investors.investor_id')
                            ->where('funding_id', $funding->id)
                            ->get();
                        $s['activity'] = json_decode(json_encode($funding), true);
                        array_push($unwindActivity, $s);
                    }
                    foreach (json_decode($startup->Milestones) as $milestone) {
                        $milestone->type = 'milestone';
                        $milestone->date = date('Y-m-d', strtotime($milestone->date_published));
                        $milestone->timestamp = $milestone->date_published;
                        $s['activity'] = json_decode(json_encode($milestone), true);
                        array_push($unwindActivity, $s);
                    }
                }
            });
        } elseif ($like->likeable_type == 'App\Models\Investor') {
            Investor::where('id', $like->likeable_id)->chunkById(Investor::count(), function ($investors) use (&$unwindActivity) {
                foreach ($investors as $investor) {
                    $i = [];
                    $i = json_decode(json_encode($investor), true);
                    $i['type'] = 'App\Models\Investor';
                    foreach (json_decode($investor->News) as $key => $new) {
                        $new->type = 'news';
                        $new->date = date('Y-m-d', strtotime($new->date_published));
                        $new->timestamp = $new->date_published;
                        $i['activity'] = json_decode(json_encode($new), true);
                        array_push($unwindActivity, $i);
                    }
                }
            });
        }
    }

    usort($unwindActivity, function ($a, $b) {
        return ($a['activity']['date'] < $b['activity']['date']) ? -1 : 1;
    });

    $startupActivitys = [];
    foreach ($unwindActivity as $activity) {
        $feed = [];
        if ($activity['type'] == 'App\Models\Investor') {
            $feed = Investor::where('id', $activity['id'])->select()->get()->toArray();
            $feed = $feed[0];
            $feed['type'] = 'App\Models\Investor';
        } elseif ($activity['type'] == 'App\Models\Startup') {
            $feed = Startup::where('id', $activity['id'])->select()->get()->toArray();
            $feed = $feed[0];
            $feed['type'] = 'App\Models\Startup';
        }

        $flag = 0;
        $count = 0;
        foreach ($startupActivitys as $startupActivity) {
            if (($startupActivity['id'] == $activity['id'] && $startupActivity['type'] == $activity['type'])) {
                $month = date('m', strtotime($startupActivity['date'])) == date('m', strtotime($activity['activity']['date']));
                $year = date('Y', strtotime($startupActivity['date'])) == date('Y', strtotime($activity['activity']['date']));

                if ($month && $year) {
                    $feed = $startupActivity;
                    $flag = 1;
                    break;
                }
            }
            ++$count;
        }

        if ($flag == 0) {
            $feed['date'] = $activity['activity']['date'];
            $feed['activitys']['total'] = 1;
            $feed['activitys']['0'] = $activity['activity'];
            $startupActivitys[$count] = $feed;
        } else {
            $feed['activitys']['total'] = count($feed['activitys']);
            $feed['activitys'][$feed['activitys']['total'] - 1] = $activity['activity'];
            $startupActivitys[$count] = $feed;
        }
    }

    Like::where('user_id', $user->id)->orderBy('created_at', 'desc')->chunkById(1, function ($like) use (&$startupActivitys) {
        foreach ($startupActivitys as $key => $startupActivity) {
            if ($startupActivity['id'] == $like[0]->likeable_id && $startupActivity['type'] == $like[0]->likeable_type) {
                array_unshift($startupActivitys, $startupActivity);
                unset($startupActivitys[$key]);
            }
        }
    });

    echo json_encode($startupActivitys);

    return view('welcome');
});

Route::get('/like', function () {
    $user = User::find(1);
    $startup = Startup::find(1);
    $investor = Investor::find(1);

    $user->like($startup);
    $user->like($investor);
});
