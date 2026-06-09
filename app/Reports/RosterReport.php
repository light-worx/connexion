<?php

namespace App\Reports;

use Illuminate\Support\Facades\Route;
use Lightworx\FilamentReports\Reports\BaseReport;
use App\Models\Individual;
use App\Models\Roster;
use App\Models\Rostergroup;
use DateTime;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use stdClass;

class RosterReport extends BaseReport
{
    protected $roster, $rostermonth, $rosteryear;

    public function __construct()
    {
        parent::__construct();
        $this->config['default_font']['family'] = 'Arial';
        $this->config['default_font']['size'] = 11;
        $this->config['page']['margins']['left'] = 10;
        $this->config['page']['margins']['right'] = 10;
        $this->config['page']['margins']['top'] = 20;
        $this->config['page']['orientation'] = 'L';
        $this->config['footer']['enabled'] = false;
    }

    public static function routes(): void
    {
        Route::get('/admin/people/rosters/{roster}/{rosteryear}/{rostermonth}', function ($rosterId,$year,$month) {
            $roster = Roster::findOrFail($rosterId);
            return (new static())->setRoster($roster,$year,$month)->handle();
        })->name('reports.roster');
    }

    public function setRoster($roster,$year,$month): static
    {
        $this->roster = $roster;
        $this->rostermonth = $month;
        $this->rosteryear = $year;
        return $this;
    }

    public function generate(): void
    {
        $this->AddPage('L');
        $this->Image(url('/') . "/images/logo_large.png",7,3,62);
        $this->SetFont('Arial', 'B', 18);
        $this->text(73,11,$this->roster->roster);
        $this->SetFont('Arial', '', 14);
        $this->SetTitle($this->roster->roster);
        $dateObj   = DateTime::createFromFormat('!m', $this->rostermonth);
        $monthName = $dateObj->format('F');
        $this->text(73,17,$monthName . " " . $this->rosteryear);
        $period=1;
        for ($i=0;$i<$period;$i++){
            $reportdate = date('F Y',strtotime($this->rosteryear . '-' . $this->rostermonth . '-01'));
            $data = $this->getRosterData(date('Y-m',strtotime($this->rosteryear . '-' . $this->rostermonth . '-01')),$this->roster->id);
            $xx = 66;
            $this->SetFont('Arial', 'B', 12);
            if (count($data['columns'])==6){
                $add=-5;
            } elseif (count($data['columns'])==5){
                $add=0;
            } else {
                $add=10;
            }
            $this->rect(10,26,280,11,'F');
            $this->SetTextColor(255,255,255);
            foreach ($data['columns'] as $week) {
                if (isset($data['midweeks'][$week])){
                    $xx=$xx+$add;
                    $this->text($xx,31,$week);
                    $this->SetFont('Arial', '', 10);
                    $this->text($xx,35,$data['midweeks'][$week]);
                    $this->SetFont('Arial', 'B', 12);
                    $xx=$xx+44;
                } else {
                    $xx=$xx+$add;
                    $this->text($xx,33,$week);
                    $xx=$xx+44;
                }
            }
            $this->SetTextColor(0,0,0);
            $yy = 42;
            $max = 1;
            $first=true;
            $minus=0;
            foreach ($data['rows'] as $key=>$col) {
                $this->SetFont('Arial', 'B', 11);
                $yy=$yy-$minus;
                $this->text(10,1+$yy,$key);
                if ($first){
                    $first=false;
                } else {
                    $this->line(10, $yy-5, 290, $yy-5);
                    $minus=0;
                }
                $xx = 22;
                $this->SetFont('Arial', '', 10.5);
                $max=1;
                foreach ($col as $kk=>$ii) {
                    if (($kk <> "id") and ($kk<>"extra")){
                        $xx=$xx+44+$add;
                        $count=0;
                        foreach ($ii as $pp){
                            if ($pp <>"-"){
                                if (strpos($pp,", ")){
                                    $this->text($xx,1+$yy+$count*5,substr($pp,2+strpos($pp,',')) . " " . substr($pp,0,strpos($pp,',')));
                                } else {
                                    $this->text($xx,1+$yy+$count*5,$pp);
                                }
                                $count++;
                            }
                            if ($count>$max){
                                $max=$count;
                                $minus = 4;
                            }
                        }
                    }
                }
                $yy=$yy+9*($max);
            }
            if (($period==2) && ($i==0)){
                if ($month==12){
                    $month=1;
                    $year=$year+1;
                } else {
                    $month=$month+1;
                }
            }
        }
        $this->Output('S');
    }

    protected function getFilename(): string
    {
        return $this->roster->roster . ' ' . now()->format('Y-M') . '.pdf';
    }

    private function getRosterData($today,$id) {
        $firstday=date('l',strtotime($today.'-01'));
        $alldays=['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
        $dday = 8 - array_search($firstday,$alldays) + array_search($this->roster->dayofweek,$alldays);
        if ($dday > 7){
            $dday=$dday-7;
        }
        $firstdate=$today . '-0' . $dday;
        $data = array();
        $rosterweeks=$this->getRosterWeeks($id,$firstdate);
        $data['columns']=$rosterweeks['columns'];
        $data['midweeks']=$rosterweeks['midweeks'];
        $groups = DB::table('rosters')->join('rostergroups', 'rosters.id', '=', 'rostergroups.roster_id')
            ->join('groups', 'rostergroups.group_id', '=', 'groups.id')
            ->select('groupname','groups.id','rostergroups.extrainfo')
            ->where('rosters.id',$id)
            ->orderBy('groupname')
            ->get();
        if ((isset($this->roster->sundayservice)) and ($this->roster->sundayservice!=='')){
            $preachergroup=new stdClass();
            $preachergroup->groupname="Preacher";
            $preachergroup->id=0;
            $preachergroup->extrainfo="";
            $groups[] = $preachergroup;
        }
        foreach ($groups as $group){
            $data['rows'][$group->groupname]['id']=$group->id;
            if ($group->extrainfo=='yes'){
                $data['rows'][$group->groupname]['extra']='yes';
            } 
            foreach ($data['columns'] as $col){
                $fixdate=date('Y-m-d',strtotime($col));
                if ($group->id==0){
                    $url="https://methodist.church.net.za/preacher/" . setting('society_id') . "/" . $this->roster->sundayservice . "/" . date('Y-m-d',strtotime($col));
                    $response=Http::get($url);
                    $extra = $response->body();
                    if ((isset($set->series)) and ($set->series->series !== "")) {
                        $extra = $extra . " (" . $set->series->series . ")";
                    }
                    $data['rows'][$group->groupname][$col][] = $extra;
                } else {
                    $dum=DB::table('rosteritems')->join('rostergroups','rosteritems.rostergroup_id','=','rostergroups.id')
                        ->join('rosters','rostergroups.roster_id','=','rosters.id')
                        ->join('groups','rostergroups.group_id','=','groups.id')
                        ->join('individual_rosteritem','individual_rosteritem.rosteritem_id','rosteritems.id')
                        ->select('individual_rosteritem.individual_id')
                        ->where('rosteritems.rosterdate','=',$fixdate)
                        ->where('groups.id',$group->id)
                        ->where('rosters.id','=',$id)
                        ->get();
                    if (count($dum)){
                        foreach ($dum as $individ) {
                            if ($individ->individual_id < 0){
                                $indivextra=Rostergroup::where('roster_id',$id)->where('group_id',$group->id)->first()->extraoptions;
                                $eoptions=explode(",",$indivextra);
                                foreach ($eoptions as $ko=>$eo){
                                    if ($individ->individual_id == -1 * (1+$ko)){
                                        $indivdata=$eo;
                                    }
                                }
                            } else {
                                $indiv=Individual::find($individ->individual_id);
                                if ($indiv){
                                    $indivdata=$indiv->surname . ', ' . $indiv->firstname;
                                }
                            }
                            if ($indiv){
                                $data['rows'][$group->groupname][$col][$indiv->id] = $indivdata;
                            } else {
                                $data['rows'][$group->groupname][$col][] = "-";
                            }
                        }
                    } else {
                        $data['rows'][$group->groupname][$col][] = "-";
                    }
                    unset($dum);
                }
            }
        }
        return $data;
    }

        private function getRosterWeeks($roster,$firstday){
        $weeks[]=$firstday;
        $ym=date('Y-m',strtotime($firstday));
        $nm=date('Y-m',strtotime($firstday . ' + 1 month'));
        for ($i=1;$i<5;$i++){
            if ($ym== date('Y-m',strtotime($firstday . ' + ' . $i * 7 . ' days'))){
                $weeks[]=date('Y-m-d',strtotime($firstday . ' + ' . $i * 7 . ' days'));
            }
        }

        // Midweeks??
        $mws=[];
        asort($weeks);
        $dum=[
            'columns' => array_values($weeks),
            'midweeks' => $mws
        ];
        return $dum;
    }
}