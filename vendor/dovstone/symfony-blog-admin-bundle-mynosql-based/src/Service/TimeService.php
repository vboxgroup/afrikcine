<?php

namespace DovStone\Bundle\SymfonyBlogAdminBundleMyNoSQLBased\Service;

use DovStone\Bundle\SymfonyBlogAdminBundleMyNoSQLBased\Service\PleaseService;
use DovStone\Bundle\SymfonyBlogAdminBundleMyNoSQLBased\Service\__Html2TextService;
use DovStone\Bundle\BlogAdminBundle\Service\__VerotUploadService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use ScssPhp\ScssPhp\Compiler;

class TimeService extends AbstractController
{
    public function __construct(PleaseService $please)
    {
        $this->please = $please;
    }

    public function getMonth($dateTime = null, $type = null, $months_prefixed = null, $ellipsis = null)
    {
        $dateTime = !is_null($dateTime) ? ( is_string($dateTime) ? new \DateTime($dateTime) : $dateTime ) : new \DateTime();
        $monthsWithout = array('janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre');
        $monthsWith = array('de janvier', 'de février', 'de mars', 'd\'avril', 'de mai', 'de juin', 'de juillet', 'd\'août', 'de septembre', 'd\'octobre', 'de novembre', 'de décembre');
        /*    $monthsWith[3] = 'd\'avril';
        $monthsWith[7] = 'd\'août';
        $monthsWith[9] = 'd\'octobre';*/
        $month = (is_null($months_prefixed)) ? $monthsWithout[intval($dateTime->format("m")) - 1] : $monthsWith[intval($dateTime->format("m")) - 1];

        if (!is_null($type) && $type == 'number') {
            $month = $dateTime->format("m");
        }

        if( is_numeric($ellipsis) ){
            if( $month == 'août' ){ $ellipsis = $ellipsis + 1; }
            $month = substr($month, 0, $ellipsis).'.';
        }

        return $month;
    }

    public function getClassicTimestamp($date, $format = null, $dateAsString = null)
    {
        $dateAsString = ($dateAsString == null) ? '' : Model::getDay($date) . ' ';
        $format == null ? $format = "d/m/Y à H:i:s" : $format;
        $timestamp = new \DateTime($date, new \DateTimeZone('UTC'));
        return ucfirst($dateAsString) . '' . $timestamp->format($format);
    }

    public function getFormat($format = "Y-m-d H:i:s", $timestamp = null)
    {
        return date($format, strtotime(is_null($timestamp) ? horodatage()->datetime : str_ireplace('/', '-', $timestamp)));
    }

    public function getFrenchDateFormatToUsFormat($date, $delimiter = '-')
    {
        $exploded = explode($delimiter, $date);
        $formatted = $exploded[2] . '-' . $exploded[1] . '-' . $exploded[0];
        return new \DateTime($formatted);
    }

    public function getFrenchDate($dateTime=null, $format = "D/d/M/Y H:i:s")
    {
        if (is_string($dateTime)) {
            $dateTime = new \DateTime($dateTime, new \DateTimeZone('UTC'));
        }
        else {
            $dateTime = is_null($dateTime) ? new \DateTime() : $dateTime;
        }
        return $this->transDateToFrench($dateTime->format($format));
    }

    public function isCorrectDateFormat($date)
    {
        $exploded = explode('-', $date);
        if (
            strlen($exploded[0]) == 4// years
            && (strlen($exploded[1]) > 0 && strlen($exploded[1]) <= 2 && $exploded[1] > 0 && $exploded[1] < 13) // month
            && (strlen($exploded[2]) > 0 && strlen($exploded[2]) <= 2 && $exploded[2] > 0 && $exploded[2] < 32) // day
        ) {
            return $date;
        }
        return false;
    }

    public function getHumanDiff($timestamp, $tokens = null)
    {
        if (!is_string($timestamp)) {
            $timestamp = $timestamp->format('Y-m-d H:i:s');
        }
        $timestamp = time() - strtotime($timestamp); // to get the time since that moment
        $timestamp = ($timestamp < 1) ? 1 : $timestamp;
        $tokens = array(
            31536000 => 'an', //year
            2592000 => 'mois', //month
            604800 => 'semaine', //week
            86400 => 'jour', //day
            3600 => 'heure', //hour
            60 => 'minute', //minute
            1 => 'seconde', //second
        );

        foreach ($tokens as $unit => $text) {
            if ($timestamp < $unit) {
                continue;
            }

            $numberOfUnits = floor($timestamp / $unit);
            return $numberOfUnits . ' ' . $text . (($numberOfUnits > 1) ? $text !== 'mois' ? 's' : '' : '');
        }
        return '';
    }

    public function getTimeAgo($timestamp, $tokens = null)
    {
        return $this->getHumanDiff($timestamp, $tokens);
    }

    public function getTimeRemaining($future_date, $format = "%d jours, %h heures, %i minutes, %s secondes")
    {
        $now = new \DateTime();
        if (is_string($future_date)) {
            $future_date = new \DateTime($future_date);
        }
        $interval = $future_date->diff($now);
        return $interval->format($format);
    }

    public function getDateTime($datetime = null)
    {
        return new \DateTime(is_null($datetime) ? date("Y-m-d H:i:s") : $datetime, new \DateTimeZone('GMT'));
    }

    public function getDaysOptions($label = 'Jour', $selected = null, $required=true)
    {
        $options = '<option value="" style="color:#000;font-weight:bold" selected '.($required?'disabled':'').'>' . $label . '</option>';
        for ($i = 1; $i < 32; $i++) {
            $ii = strlen($i) == 1 ? '0' . $i : $i;
            $options .= '<option value="' . $i . '" '.($selected==$i?'selected':'').'>' . $ii . '</option>';
        }
        return $options;
    }

    public function getMonthsOptions($label = 'Mois', $selected = null, $required=true, $short = false)
    {
        $options = $short
        ? '<option value="" style="color:#000;font-weight:bold" selected '.($required?'disabled':'').'>' . $label . '</option>
			<option value="01" '.($selected==1?'selected':'').'>jan</option>
			<option value="02" '.($selected==2?'selected':'').'>fév</option>
			<option value="03" '.($selected==3?'selected':'').'>mar</option>
			<option value="04" '.($selected==4?'selected':'').'>avr</option>
			<option value="05" '.($selected==5?'selected':'').'>mai</option>
			<option value="06" '.($selected==6?'selected':'').'>jui</option>
			<option value="07" '.($selected==7?'selected':'').'>Jui</option>
			<option value="08" '.($selected==8?'selected':'').'>aoû</option>
			<option value="09" '.($selected==9?'selected':'').'>sep</option>
			<option value="10" '.($selected==10?'selected':'').'>oct</option>
			<option value="11" '.($selected==11?'selected':'').'>nov</option>
			<option value="12" '.($selected==12?'selected':'').'>déc</option>'
        : '<option value="" style="color:#000;font-weight:bold" selected '.($required?'disabled':'').'>' . $label . '</option>
			<option value="01" '.($selected==1?'selected':'').'>Janvier</option>
			<option value="02" '.($selected==2?'selected':'').'>Février</option>
			<option value="03" '.($selected==3?'selected':'').'>Mars</option>
			<option value="04" '.($selected==4?'selected':'').'>Avril</option>
			<option value="05" '.($selected==5?'selected':'').'>Mai</option>
			<option value="06" '.($selected==6?'selected':'').'>Juin</option>
			<option value="07" '.($selected==7?'selected':'').'>Juillet</option>
			<option value="08" '.($selected==8?'selected':'').'>Août</option>
			<option value="09" '.($selected==9?'selected':'').'>Septembre</option>
			<option value="10" '.($selected==10?'selected':'').'>Octobre</option>
			<option value="11" '.($selected==11?'selected':'').'>Novembre</option>
			<option value="12" '.($selected==12?'selected':'').'>Décembre</option>';
        return $options;
    }

    public function getYearsOptions($label = 'Années', $selected = null, $required=true, $from = 1950, $to=null, $order='asc')
    {
        $years = $to ?? (new \DateTime())->format('Y');
        $from = $from ?? (new \DateTime())->format('Y');
        $options = '<option value="" style="color:#000;font-weight:bold" selected '.($required?'disabled':'').'>' . $label . '</option>';
        $order = strtoupper($order);
        if( $order == 'ASC' ){
            for ($i = $from; $i < $years + 1; $i++) {
                $options .= '<option value="' . $i . '" '.($selected==$i?'selected':'').'>' . $i . '</option>';
            }
        }
        else {
            for ($i = $from; $i >= $years; $i--) {
                $options .= '<option value="' . $i . '" '.($selected==$i?'selected':'').'>' . $i . '</option>';
            }
        }
        return $options;
    }

    public function convertToHoursMins($time, $format = '%02dh%02dmin')
    {
        //echo convertToHoursMins(250, '%02d hours %02d minutes'); // should output 4 hours 17 minutes

        if ($time < 1) {
            return;
        }
        $hours = floor($time / 60);
        $minutes = ($time % 60);

        return $hours == 0 ? sprintf('%02dmin', $minutes) : sprintf($format, $hours, $minutes);
    }

    public function getYearsOld($birthdate)
    {
        if( is_null($birthdate) ){
            return '?';
        }
        if (!is_string($birthdate)) {
            $birthdate = $birthdate->format('Y-m-d');
        }
        //$dateOfBirth = "17-10-1985";
        $today = date("Y-m-d");
        $diff = date_diff(date_create($birthdate), date_create($today));
        return $diff->format('%y');
    }

    public function ellipsisDate($dateTime, $format = "D d M Y")
    {
        if( is_string($dateTime) ){
            $dateTime = new \DateTime($dateTime, new \DateTimeZone('UTC'));
        }
        $datetime = $this->getFrenchDate(is_null($dateTime) ? date("Y-m-d") : $dateTime, "D/d/M/Y");

        $x = explode(' ', $datetime);
        //
        $M = substr($x[2], 0, 4);
        $formatArr = [
            'D' => substr($x[0], 0, 3).'.',
            'd' => $x[1],
            'M' => ($M == 'aôu') ? 'aoû.' : ($M == 'déc' ? 'déc.' : substr($M, 0, 3).'.'),
            'Y' => $x[3]
        ];
        //
        $output = '';
        $xf = explode(' ', $format);
        foreach($xf as $f){
            if(isset($formatArr[$f])){
                $output .= "$formatArr[$f] ";
            }
        }
        return trim($output);
    }

    private function transDateToFrench($date)
    {
        $xploded = explode('/', $date);
        $arr = [
            'Mon' => 'lundi',
            'Tue' => 'mardi',
            'Wed' => 'mercredi',
            'Thu' => 'jeudi',
            'Fri' => 'vendredi',
            'Sat' => 'samedi',
            'Sun' => 'dimanche',

            'Jan' => 'janvier',
            'Feb' => 'février',
            'Mar' => 'mars',
            'Apr' => 'avril',
            'May' => 'mai',
            'Jun' => 'juin',
            'Jul' => 'juillet',
            'Aug' => 'aôut',
            'Sep' => 'septembre',
            'Oct' => 'octobre',
            'Nov' => 'novembre',
            'Dec' => 'décembre',
        ];

        $date = '';
        foreach ($xploded as $partial) {
            if (isset($arr[$partial])) {
                $date .= $arr[$partial] . ' ';
            } else {
                $date .= $partial . ' ';
            }
        }
        return $date;
    }
}
