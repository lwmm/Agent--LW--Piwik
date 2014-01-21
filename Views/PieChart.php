<?php

namespace AgentPiwik\Views;

class PieChart
{
    public function render($config, $searchwords)
    {
        $array = $this->prepareAarray($searchwords);
        #print_r($array);die();
        $view = new \lw_view(dirname(__FILE__) . '/Templates/PieChart.phtml');
        $view->jqPlotMin = $config["url"]["media"]."jquery/jqplot/jquery.jqplot.min.js";
        $view->jqPlotPieRender = $config["url"]["media"]."jquery/jqplot/plugins/jqplot.pieRenderer.min.js";
        
        $view->seArray = $array;
        
        return $view->render();
    }
    
    private function prepareAarray($searchwords)
    {
        $array = array();
        $i = 1;
        foreach($searchwords as $word){
            foreach($word["searchengine"] as $se){
                $array[$i][] = array("label" => $se["label"], "visits" => $se["nb_visits"], "title" => $word["label"]);
            }
            $i++;
        }
        
        return $array;
    }
}