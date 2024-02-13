<?php

namespace App\Services\FeedbackControlService;

use Parser;
use Carbon\Carbon;
use App\Models\ClientProfile;
use App\Models\ProfileFeedback;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Services\Telegram\TelegramBotApi;
use Illuminate\Contracts\Database\Eloquent\Builder;

class FeedbackControlService
{
    public function checkFeedback()
    {
        $profiles = $this->getProfiles();
        $profile_feedback = [];
        $text = '';
        foreach($profiles as $profile){
            $feedback_count = Parser::parserFeedbackCount($profile->yandex_map_url);
            if($feedback_count !== false){
                Log::info(now() . ' - ' . $profile->title);
                $profile_feedback[] =[
                    'feedback_count' => $feedback_count,
                    'client_profile_id' => $profile->id,
                    'created_at' => NOW()
                ];
            }

            if($prev = $profile->feedbacks()->latest()->first()){
                $difference = $feedback_count - $prev['feedback_count'];
                if($difference > 0){
                    $text .= "{$profile->title} - $difference \n";
                    if(isset($profile->client->feedback_user->name)){
                        $text .= "Ответственный за отзывы - {$profile->client->feedback_user->name} \n\n"; 
                    }
                    ProfileFeedback::insert($profile_feedback);
                }
            }
        }
        
        if(!empty($text)){
            $text = 'Отзывы Яндекс бизнес - ' . Carbon::now()->format('d/m/Y - H:i:s') . "\n" . $text;
            TelegramBotApi::sendMessage($text,env('TELEGRAM_FEEDBACK_CHAT_ID'), env('TELEGRAM_FEEDBACK_BOT_TOKEN'));
        }
    }

    protected function getProfiles()
    {
        $profile = ClientProfile::where('status', 1)
                ->whereHas('client',fn ($query) => $query->where('status', 1))
                ->where('id', '>=', $this->getProfileId())
                ->with(['client' => function (Builder $query) {
                    $query->with('feedback_user');
                }])
                ->orderBy('id', 'ASC')
                ->limit(1)
                ->get();
        if ($profile->isEmpty()) {
            Cache::put('profile_id', 0);
            $this->checkFeedback();
        }else{
            Cache::put('profile_id', ($profile->first()->id + 1));
        }
        return $profile;
    }

    protected function getProfileId()
    {
        $id = 0;
        if(Cache::has('profile_id')){
            $id = Cache::get('profile_id');
        }else{
            Cache::put('profile_id', 0);
        }
     
        return $id;
    }

    protected function getProfilesForCount($date2)
    {
        return ClientProfile::where('status', 1)
                ->whereHas('client',fn ($query) => $query->where('status', 1))
                ->with(['client' => function (Builder $query) {
                    $query->with('feedback_user');
                }])
                ->where('created_at', '<', $date2)
                ->orderBy('title', 'ASC')
                ->get();
    }

    public function getCountForPeriod($date1, $date2)
    {
        $profiles = $this->getProfilesForCount($date2);
        $result = [
            [
                'title' => 'Название',
                'start' => 'Начало периода',
                'end' => 'Окончание периода',
                'feedback_user' => 'Ответственный за отзывы',
            ]
        ];
        foreach($profiles as $profile){
            $result[] = [
                'title' => $profile->title,
                'start' => $this->getStartPeriodCount($profile, $date1, $date2),
                'end' => $this->getEndPeriodCount($profile, $date1, $date2),
                'feedback_user' => $profile->client->feedback_user?->name,

            ];
        }
        return $result;
    }

    protected function getStartPeriodCount($profile, $date1, $date2 )
    {
        if(! $start = $profile->feedbacks()
                ->orderBy('created_at', 'DESC')
                ->where('created_at', '<', $date1)
                ->where('feedback_count', '!=', 0)
                ->select('feedback_count')
                ->first()
        ){
            $start = $profile->feedbacks()
                ->orderBy('created_at', 'ASC')
                ->where('created_at', '>=', $date1)
                ->where('created_at', '<', $date2)
                ->where('feedback_count', '!=', 0)
                ->select('feedback_count')
                ->first();
        }


        if($start){
            return $start->feedback_count;
        }

        return '-';

    }

    protected function getEndPeriodCount($profile, $date1, $date2 )
    {
        if($end = $profile->feedbacks()
            ->orderBy('created_at', 'DESC')
            ->where('created_at', '<=', $date2)
            ->where('created_at', '>', $date1)
            ->select('feedback_count')
            ->first()
        ){
            return $end->feedback_count;
        }
        return '-';

    }


}