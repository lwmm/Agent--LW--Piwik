<?php

namespace AgentPiwik\Views;

class ShowData
{    
    public function render($config, $page_id = false,$filterArray = false,$use_default_portal = false)
    {
        $piwikRequest = new \AgentPiwik\Model\PiwikRequest($config);
        
        $view = new \lw_view(dirname(__FILE__) . '/Templates/PiwikForm.phtml');
        
        $view->adress = $config["url"]["client"]."admin.php?obj=piwik";
        $view->bootstrapCSS = $config["url"]["media"] . "bootstrap/css/bootstrap.min.css";
        $view->bootstrapJS = $config["url"]["media"] . "bootstrap/js/bootstrap.min.js";
        $view->SRCspin = $config["url"]["media"]."modules/spinloader/spin.js";
        $view->SRCspinmin= $config["url"]["media"]."modules/spinloader/spin.min.js";
        $view->jqUIcss = $config["url"]["media"]."jquery/ui/css/smoothness/jquery-ui-1.8.7.custom.css";
        $view->jqUI = $config["url"]["media"]."jquery/ui/jquery-ui-1.8.custom.min.js";
        
        $view->pageId = $page_id;
        
        if($use_default_portal == false){
            $view->no_default_portal = true;
            $portals = $view->allPortals = $piwikRequest->getAllPortals();
            foreach($portals as $portal){
                if($portal["idsite"] == $page_id){
                    $view->pagename = $portal["name"];
                }
            }
        }else{
            if($use_default_portal == "PIWIK - Online Demodaten"){
                $view->demo_info = true;
            }
            $view->pagename = $use_default_portal;
        }

        $view->timeFilter = $filterArray["time_filter"];

        
        if($page_id){
            $pieView = new \AgentPiwik\Views\PieChart();
            $dataArray = $piwikRequest->collectData($page_id, $filterArray);
            
            $view->colorArray = array("#788eff", "#fffa78", "#b8f5fb", "#aeff78", "#cb78ff", "#ff7878", "#ffbb78", "#d4ff78", "#78ffa4", "#ab78ff");
            $view->page_visits = $dataArray["page_visits"];
            $view->browser = $dataArray["browser"];
            $view->servertime_visits = $dataArray["servertime_visits"];
            $view->countries = $dataArray["countries"];
            $view->refers = $dataArray["refers"];
            $view->uniq_visitors = $dataArray["todayData"]["nb_uniq_visitors"];
            $view->visits = $dataArray["todayData"]["nb_visits"];
            $view->actions = $dataArray["todayData"]["nb_actions_per_visit"];
            $view->visit_time = $dataArray["todayData"]["avg_time_on_site"];
            
            $view->mostUsedPages = $piwikRequest->getMostUsedPages($page_id, $filterArray);
            $seachrResults = $view->searchWords = $piwikRequest->getMostUsedSearchwords($page_id, $filterArray);
            $view->searchPies = $pieView->render($config, $seachrResults);
            $base = str_replace("?module=API", "",$config["piwik"]["base"]);            
            $view->piwikBase = $base;
        }
        return $view->render();
    }
}