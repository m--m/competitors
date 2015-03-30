<?php

date_default_timezone_set('Europe/Kiev');

include 'simple_html_dom.php';

class Competitor {

    public $sites;

    public function __construct() {

        $this->sites['dom.ria.ua'] = array(
            'http://dom.ria.com/ru/search/?category=0&realty_type=0&advert_type=0&state_id=0&realty_id=&period=0&textsearch='
        );
        $this->sites['fn.ua'] = array('http://fn.ua/');
        $this->sites['olx.ua'] = array('http://olx.ua/nedvizhimost/');
        $this->sites['100realty.ua'] = array('http://100realty.ua/');
        $this->sites['mirkvartir.ua'] = array('http://mirkvartir.ua/offers/');

        $this->sites['meget.kiev.ua'] = array(
            'http://meget.kiev.ua/arenda-kvartir/',
            'http://meget.kiev.ua/prodazha-kvartir/',
            'http://meget.kiev.ua/prodazha-domov/',
            'http://meget.kiev.ua/prodazha-pomescheniy/'
        );

        $this->sites['address.ua'] = array(
            'http://address.ua/sdajut-kvartiry/',
            'http://address.ua/sdajut-komnata/',
            'http://address.ua/prodajut-kvartiry/',
            'http://address.ua/prodajut-dom/',
            'http://address.ua/prodajut-ofis/'
        );
    }

    public function DomRiaUA($urls) {
        $url = $urls[0];

        
        $html = file_get_contents($url);
       
        $html = str_get_html($html);
      
        foreach ($html->find('#filter_total_count') as $cur) {
            return strip_tags($cur);
        }
    }

    public function FnUA($urls) {
        $url = $urls[0];
        $html = file_get_html($url);
        if (strlen($html) > 200) {
            foreach ($html->find('.search-ads-total') as $cur) {
                $e = explode(" ", $cur);

                return $e[2];
            }
        }
        return 0;
    }

    public function realty100Ua($urls) {
        $url = $urls[0];
        $html = file_get_html($url);
        $sum = 0;
        foreach ($html->find('.left_bmn') as $cur) {
            $cur = strip_tags($cur);
            $cur = str_replace(" ", "", $cur);
            $sum += (int) $cur;
        }

        return $sum;
    }

    public function MirkvartirUA($urls) {
        $url = $urls[0];
        $html = file_get_html($url);
        $sum = 0;

        foreach ($html->find('h3') as $cur) {

            $cur = strip_tags($cur);
            preg_match_all("/([0-9]+)/", $cur, $all);

            if (isset($all[0][0])) {
                $sum += $all[0][0];
            }
        }

        return $sum;
    }

    public function MegetKievUa($urls) {
        $sum = 0;
        foreach ($urls as $url) {

            $html = file_get_html($url);

            foreach ($html->find('.search-result-info') as $cur) {

                $cur = str_replace("Найдено:", "", $cur);
                $cur = strip_tags(str_replace("объявлений", "", $cur));

                $cur = (int) str_replace(" ", "", $cur);

                $sum += strip_tags($cur);
            }
        }

        return $sum;
    }

    public function AddressUa($urls) {
        $sum = 0;
        foreach ($urls as $url) {
            $html = file_get_html($url);

            foreach ($html->find('.tab_head_table') as $cur) {
                $cur = strip_tags($cur);
                $e = explode("объект", $cur);
                $cur = $e[0];

                $cur = (int) str_replace(" ", "", $cur);
                $sum += $cur;
            }
        }

        return $sum;
    }

    public function OlxUa($urls) {
        $url = $urls[0];

        $html = file_get_html($url);

        foreach ($html->find('span[class=color-2 normal indexed-int]') as $cur) {
            $cur = strip_tags($cur);
            $cur = (int) str_replace(" ", "", $cur);
            return $cur;
        }
    }

    public function proc() {

        $h = fopen("competitors.csv", "a");
        fwrite($h, date("Y-m-d", time()) . ",");
        foreach ($this->sites as $siteName => $url) {
            echo $siteName;
            if ($siteName == "dom.ria.ua") {
                fwrite($h, "dom.ria.ua," . $this->DomRiaUA($url) . ",");
            }

            if ($siteName == "fn.ua") {

                fwrite($h, "fn.ua," . $this->FnUA($url) . ",");
            }


            if ($siteName == "100realty.ua") {

                fwrite($h, "100realty.ua," . $this->realty100Ua($url) . ",");
            }


            if ($siteName == "mirkvartir.ua") {

                fwrite($h, "mirkvartir.ua," . $this->MirkvartirUA($url) . ",");
            }


            if ($siteName == "meget.kiev.ua") {

                fwrite($h, "meget.kiev.ua," . $this->MegetKievUa($url) . ",");
            }


            if ($siteName == "address.ua") {

                fwrite($h, "address.ua," . $this->AddressUa($url) . ",");
            }

            if ($siteName == "olx.ua") {

                fwrite($h, "olx.ua," . $this->OlxUa($url) . ",");
            }
        }
        fwrite($h, "\n");
    }

}

$comp = new Competitor();
$comp->proc();
