<?php
namespace AgentPiwik\Model;

class PiwikRequest
{
    protected $config;
            
    function __construct($config) 
    {
        $this->config = $config;
    }
    
    function collectData($page_id,$filterArray)
    {        
        $base = $this->config['piwik']['base'];
        
        // REQUEST FUER TAGESINFORMATIONEN
        
        $request_today="&method=VisitsSummary.get".
                        "&idSite=".$page_id.
                        "&period=day".
                        "&date=today".
                        "&format=PHP".
                        "&filter_limit=20".
                        "&token_auth=".$this->config['piwik']['token_auth'];
        
        $todayData = unserialize(file_get_contents($base.$request_today));
        // GRAPHEN REQUESTS :
        
        if($filterArray["time_filter"] == "last_week"){
            $period_page_visits         = "day";    $date_page_visits       = "last7";
            $period_browser             = "week";   $date_browser           = "today";
            $period_servertime_visits   = "week";   $date_servertime_visits = "today";
            $period_countries           = "week";   $date_countries         = "today";
            $period_refers               = "day";   $date_refers            = "last7";
            if($todayData["nb_visits"] == 0)        $date_refers            = "previous7";
        }
        if($filterArray["time_filter"] == "last_month"){
            $period_page_visits         = "day";    $date_page_visits       = "last30";
            $period_browser             = "month";  $date_browser           = "today";
            $period_servertime_visits   = "month";  $date_servertime_visits = "today";
            $period_countries           = "month";  $date_countries         = "today";
            $period_refers              = "day";    $date_refers            = "last30";
            if($todayData["nb_visits"] == 0)        $date_refers            = "previous30";
        }
        if($filterArray["time_filter"] == "last_year"){
            $period_page_visits         = "month";  $date_page_visits       = "last12";
            $period_browser             = "year";   $date_browser           = "today";
            $period_servertime_visits   = "year";   $date_servertime_visits = "today";
            $period_countries           = "year";   $date_countries         = "today";
            $period_refers              = "month";  $date_refers            = "last12";
        }
        
        
        $page_visits = $base.
                        "&method=ImageGraph.get".
                        "&graphType=evolution".
                        "&idSite=".$page_id.
                        "&period=".$period_page_visits.
                        "&date=".$date_page_visits.
                        "&apiModule=VisitsSummary".
                        "&apiAction=get".
                        "&showLegend=1".
                        "&width=980".
                        "&height=200".
                        "&fontSize=9".
                        "&aliasedGraph=1".
                        "&token_auth=".$this->config['piwik']['token_auth'];
        #50% width old 490px;
        $browser = $base.
                        "&method=ImageGraph.get".
                        "&graphType=horizontalBar".
                        "&idSite=".$page_id.
                        "&period=".$period_browser.
                        "&date=".$date_browser.
                        "&apiModule=UserSettings".
                        "&apiAction=getBrowser".
                        "&showLegend=1".
                        "&width=980".
                        "&height=200".
                        "&fontSize=9".
                        "&aliasedGraph=1".
                        "&token_auth=".$this->config['piwik']['token_auth'];
        
        $servertime_visits = $base.
                        "&method=ImageGraph.get".
                        "&graphType=verticalBar".
                        "&idSite=".$page_id.
                        "&period=".$period_servertime_visits.
                        "&date=".$date_servertime_visits.
                        "&apiModule=VisitTime".
                        "&apiAction=getVisitInformationPerServerTime".
                        "&showLegend=1".
                        "&width=980".
                        "&height=200".
                        "&fontSize=9".
                        "&aliasedGraph=1".
                        "&token_auth=".$this->config['piwik']['token_auth'];
        
        $countries = $base.
                        "&method=ImageGraph.get".
                        "&graphType=horizontalBar".
                        "&idSite=".$page_id.
                        "&period=".$period_countries.
                        "&date=".$date_countries.
                        "&apiModule=UserCountry".
                        "&apiAction=getCountry".
                        "&showLegend=1".
                        "&width=980".
                        "&height=200".
                        "&fontSize=9".
                        "&aliasedGraph=1".
                        "&token_auth=".$this->config['piwik']['token_auth'];
        
        $refers = $base.
                        "&method=ImageGraph.get".
                        "&graphType=evolution".
                        "&idSite=".$page_id.
                        "&period=".$period_refers.
                        "&date=".$date_refers.
                        "&apiModule=Referers".
                        "&apiAction=getRefererType".
                        "&showLegend=1".
                        "&width=980".
                        "&height=200".
                        "&fontSize=9".
                        "&aliasedGraph=1".
                        "&token_auth=".$this->config['piwik']['token_auth'];
        
        
        $array = array(
            "page_visits" => $page_visits,
            "browser" => $browser,
            "servertime_visits" => $servertime_visits,
            "countries" => $countries,
            "refers" => $refers,
            "todayData" => $todayData
        );
        
        return $array;
    }
    
    function getMostUsedPages($page_id,$filterArray)
    {        
        if($filterArray["time_filter"] == "last_week"){
            $period           = "week";   $date         = "today";
        }
        if($filterArray["time_filter"] == "last_month"){
            $period           = "month";  $date         = "today";
        }
        if($filterArray["time_filter"] == "last_year"){
            $period           = "year";   $date         = "today";
        }
        
        $base = $this->config['piwik']['base'];

        $request =  "&method=Actions.getPageUrls".
                    "&idSite=".$page_id.
                    "&period=".$period.
                    "&date=".$date.
                    "&format=PHP".
                    "&token_auth=".$this->config['piwik']['token_auth'];


        $visits = unserialize(file_get_contents($base.$request));
        
        if(is_array($visits)){
            $sortArray = $visits;
            foreach($visits as $key => $array) {
                $sortArray[$key] = $array["nb_visits"];
            }
            array_multisort($sortArray, SORT_DESC, SORT_NUMERIC, $visits);
            
            for($i = 0; $i <= count($visits)-1; $i++){
                $secs = $visits[$i]["entry_sum_visit_length"];
                $days  = intval($secs / (60 * 60 * 24));
                $secs  = $secs % (60 * 60 * 24);
                $hours = intval($secs / (60 * 60));
                $secs  = $secs % (60 * 60);
                $mins  = intval($secs / 60);
                $secs  = $secs % 60;

                if($days == 0){
                    $time = $hours."h ".$mins."m ".$secs."s";
                }
                if($days == 0 & $hours == 0){
                    $time = $mins."m ".$secs."s";
                }
                if($days == 0 & $hours == 0 & $mins == 0){
                    $time = $secs."s";
                }
                if($days != 0 & $hours != 0 & $mins != 0){
                    $time = $days."d ".$hours."h ".$mins."m ".$secs."s";
                }
                $visits[$i]["time_visit_sum_length_pretty"] = $time;
            }
        }
        return $visits;
    }
    
    function getMostUsedSearchwords($page_id,$filterArray)
    {        
        if($filterArray["time_filter"] == "last_week"){
            $period           = "week";   $date         = "today";
        }
        if($filterArray["time_filter"] == "last_month"){
            $period           = "month";  $date         = "today";
        }
        if($filterArray["time_filter"] == "last_year"){
            $period           = "year";   $date         = "today";
        }
        
        $base = $this->config['piwik']['base'];

        $request_searchwords =  "&method=Referers.getKeywords".
                                "&idSite=".$page_id.
                                "&period=".$period.
                                "&date=".$date.
                                "&format=PHP".
                                "&token_auth=".$this->config['piwik']['token_auth'];

        $searchwords = unserialize(file_get_contents($base.$request_searchwords));
        
        if(!empty($searchwords)){
            $sortArray = $searchwords;
            foreach($searchwords as $key => $array) {
                $sortArray[$key] = $array["nb_visits"];
            }
            array_multisort($sortArray, SORT_DESC, SORT_NUMERIC, $searchwords);
            $count = count($searchwords);
            for($i = 0; $i <= $count; $i++){
                if($i > 9){
                    unset($searchwords[$i]);
                }else{     
                    if(!empty($searchwords[$i])){
                        $request_searchengines ="&method=Referers.getSearchEnginesFromKeywordId".
                                                "&idSite=".$page_id.
                                                "&period=".$period.
                                                "&date=".$date.
                                                "&idSubtable=".$searchwords[$i]['idsubdatatable'].
                                                "&format=PHP".
                                                "&token_auth=".$this->config['piwik']['token_auth']; 

                        $searchwords[$i]["searchengine"] = unserialize(file_get_contents($base.$request_searchengines));

                        if(!empty($searchwords[$i]["nb_visits"])){
                            for($k = 0 ; $k <= count($searchwords[$i]["searchengine"])-1; $k++) {
                                $percent = 100 / $searchwords[$i]["nb_visits"] * $searchwords[$i]["searchengine"][$k]["nb_visits"];
                                $percent = substr($percent, 0, strpos($percent, ".") + 3);
                                $searchwords[$i]["searchengine"][$k]["use_in_percent"] =  $percent;
                            }
                        }
                    }
                }
            }
            return $searchwords;
        }
    }
    
    function getAllPortals()
    {
        $base = $this->config['piwik']['base'];

        $request =  "&method=SitesManager.getAllSites".
                    "&format=PHP".
                    "&token_auth=".$this->config['piwik']['token_auth'];
        
        $portals = unserialize(file_get_contents($base.$request));
        
        foreach ($portals as $nr => $page){
            $name[$nr] = $page['name'];
        }
        array_multisort($name, SORT_ASC, $portals);
        
        return $portals;
    }
    
}