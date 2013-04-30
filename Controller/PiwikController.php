<?php

namespace AgentPiwik\Controller;

class PiwikController
{

    protected $config;
    protected $response;
    protected $piwikRequest;
    protected $request;

    public function __construct($config, $response, $request)
    {
        $this->config = $config;
        $this->response = $response;
        $this->request = $request;
        $this->piwikRequest = new \AgentPiwik\Model\PiwikRequest($config);
    }

    public function execute()
    {
        $view = new \AgentPiwik\Views\ShowData();

        if (!array_key_exists("piwik", $this->config)) {
            $this->config["piwik"] = array(
                "token_auth" => "anonymous",
                "base" => "http://demo.piwik.org/index.php?module=API",
                "default_portal" => ""
            );
            $use_default_portal = "PIWIK - Online Demodaten";
            $page_id = 7;

            $this->response->setOutputByKey("AgentPiwik", $view->render($this->config, $page_id, $this->setFilterArray(), $use_default_portal));
        }
        elseif (!empty($this->config["piwik"]["default_portal"])) {
            $portals = $this->piwikRequest->getAllPortals();
            for ($i = 0; $i <= count($portals) - 1; $i++) {
                if ($portals[$i]["name"] == $this->config["piwik"]["default_portal"]) {
                    $use_default_portal = $portals[$i]["name"];
                    $this->response->setOutputByKey("AgentPiwik", $view->render($this->config, $portals[$i]["idsite"], $this->setFilterArray(), $use_default_portal));
                }
            }
        }
        elseif ($this->request->getInt("sent") == 1) {
            $this->response->setOutputByKey("AgentPiwik", $view->render($this->config, $this->request->getInt("selected_page"), $this->setFilterArray(), false));
        }
        else {
            $this->response->setOutputByKey("AgentPiwik", $view->render($this->config, false, $this->setFilterArray(), false));
        }
    }

    private function setFilterArray()
    {
        if (!$this->request->getRaw("time_filter")) {
            $filterArray["time_filter"] = "last_week";
        }
        else {
            $filterArray = array(
                "time_filter" => $this->request->getRaw("time_filter")
            );
        }
        return $filterArray;
    }

}