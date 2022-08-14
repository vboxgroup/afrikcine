<?php

namespace DovStone\Bundle\SymfonyBlogAdminBundleMyNoSQLBased\Service;

use Twig\Markup;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use DovStone\Bundle\SymfonyBlogAdminBundleMyNoSQLBased\Service\PleaseService;

class MixService extends AbstractController
{
    public function __construct(PleaseService $please)
    {
        $this->please = $please;
    }

    public function getIp()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }

    public function getRef($length=5)
    {
        return substr(md5(substr(uniqid(''), 0, 20)), 0, $length);
    }


    public function arrayKeysExists(array $keys, array $arr)
    {
       return !array_diff_key(array_flip($keys), $arr);
    }

    public function arrayMergeRecursiveEx(array $array1, array $array2)
    {
        $merged = $array1;
        foreach ($array2 as $key => & $value) {
            if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
                $merged[$key] = $this->arrayMergeRecursiveEx($merged[$key], $value);
            } else if (is_numeric($key)) {
                 if (!in_array($value, $merged)) {
                    $merged[] = $value;
                 }
            } else {
                $merged[$key] = $value;
            }
        }
        return $merged;
    }

    public function randomify($list, $limit=null)
    {
        $randomified = [];
        foreach($list as $each){
            $randomified[rand()] = $each;
        }
        ksort($randomified);
        return array_slice($randomified, 0, $limit ?? count($randomified));
    }
    
    public function getCountriesOptions($selected="Côte d'Ivoire", $countries = null)
    {
        $countries = $countries ?? ["Afghanistan","Afrique du Sud","Albanie","Algerie","Allemagne","Andorre","Angola","Antigua-et-Barbuda","Arabie saoudite","Argentine","Armenie","Australie","Autriche","Azerbaidjan","Bahamas","Bahrein","Bangladesh","Barbade","Belau","Belgique","Belize","Bénin","Bhoutan","Bielorussie","Birmanie","Bolivie","Bosnie-Herzégovine","Botswana","Bresil","Brunei","Bulgarie","Burkina","Burundi","Cambodge","Cameroun","Canada","Cap-Vert","Chili","Chine","Chypre","Colombie","Comores","Congo","Cook","Corée du Nord","Corée du Sud","Costa Rica","Côte d'Ivoire","Croatie","Cuba","Danemark","Djibouti","Dominique","Egypte","Émirats arabes unis","Equateur","Erythree","Espagne","Estonie","Etats-Unis","Ethiopie","France","Fidji","Finlande","Gabon","Gambie","Georgie","Ghana","Grèce","Grenade","Guatemala","Guinée","Guinée-Bissao","Guinée équatoriale","Guyana","Haiti","Honduras","Hongrie","Inde","Indonesie","Iran","Iraq","Irlande","Islande","Israël","Italie","Jamaique","Japon","Jordanie","Kazakhstan","Kenya","Kirghizistan","Kiribati","Koweit","Laos","Lesotho","Lettonie","Liban","Liberia","Libye","Liechtenstein","Lituanie","Luxembourg","Macedoine","Madagascar","Malaisie","Malawi","Maldives","Mali","Malte","Maroc","Marshall","Maurice","Mauritanie","Mexique","Micronesie","Moldavie","Monaco","Mongolie","Mozambique","Namibie","Nauru","Nepal","Nicaragua","Niger","Nigeria","Niue","Norvège","Nouvelle-Zelande","Oman","Ouganda","Ouzbekistan","Pakistan","Panama","Papouasie-Nouvelle Guinee","Paraguay","Pays-Bas","Perou","Philippines","Pologne","Portugal","Qatar","Republique centrafricaine","Republique dominicaine","Republique tcheque","Roumanie","Royaume-Uni","Russie","Rwanda","Saint-Christophe-et-Nieves","Sainte-Lucie","Saint-Marin","Saint-Siège","Saint-Vincent-et-les Grenadines","Salomon","Salvador","Samoa occidentales","Sao Tome-et-Principe","Senegal","Seychelles","Sierra Leone","Singapour","Slovaquie","Slovenie","Somalie","Soudan","Sri Lanka","Suède","Suisse","Suriname","Swaziland","Syrie","Tadjikistan","Tanzanie","Tchad","Thailande","Togo","Tonga","Trinite-et-Tobago","Tunisie","Turkmenistan","Turquie","Tuvalu","Ukraine","Uruguay","Vanuatu","Venezuela","Viet Nam","Yemen","Yougoslavie","Zaire","Zambie","Zimbabwe"];

        $options = '';
        foreach ($countries as $c) {
            $options .= '<option '. ($selected==$c?'selected':'') .' value="'.$c.'">'.$c.'</option>';
        }
        return new Markup($options, 'UTF-8');
    }
    
    public function getCountriesCallCode($selected=225, $countries_codes = null)
    {
        $countries_codes = $countries_codes ?? ["Etats Unis d'Amérique" => 1, "Canada" => 1, "Fédération russe" => 7, "Kazakhstan" => 7, "Ouzbekistan" => 7, "Egypte" => 20, "Afrique du Sud" => 27, "Grèce" => 30, "Pays-Bas" => 31, "Belgique" => 32, "France" => 33, "Espagne" => 34, "Hongrie" => 36, "Italie" => 39, "Vatican" => 39, "Roumanie" => 40, "Liechtenstein" => 41, "Suisse" => 41, "Autriche" => 43, "Royaume-Uni" => 44, "Danemark" => 45, "Suède" => 46, "Norvège" => 47, "Pologne" => 48, "Allemagne" => 49, "Pérou" => 51, "Mexique Centre" => 52, "Cuba" => 53, "Argentine" => 54, "Brésil" => 55, "Chili" => 56, "Colombie" => 57, "Vénézuela" => 58, "Malaisie" => 60, "Australie" => 61, "Ile Christmas" => 61, "Indonésie" => 62, "Philippines" => 63, "Nouvelle-Zélande" => 64, "Singapour" => 65, "Thaïlande" => 66, "Japon" => 81, "Corée du Sud" => 82, "Viêt-Nam" => 84, "Chine" => 86, "Turquie" => 90, "Inde" => 91, "Pakistan" => 92, "Afghanistan" => 93, "Sri Lanka" => 94, "Union Birmane" => 95, "Iran" => 98, "Maroc" => 212, "Algérie" => 213, "Tunisie" => 216, "Libye" => 218, "Gambie" => 220, "Sénégal" => 221, "Mauritanie" => 222, "Mali" => 223, "Guinée" => 224, "Côte d'Ivoire" => 225, "Burkina Faso" => 226, "Niger" => 227, "Togo" => 228, "Bénin" => 229, "Maurice" => 230, "Libéria" => 231, "Sierra Leone" => 232, "Ghana" => 233, "Nigeria" => 234, "République du Tchad" => 235, "République Centrafricaine" => 236, "Cameroun" => 237, "Cap-Vert" => 238, "Sao Tomé-et-Principe" => 239, "Guinée équatoriale" => 240, "Gabon" => 241, "Bahamas" => 242, "Congo" => 242, "Congo Zaïre (Rep. Dem.)" => 243, "Angola" => 244, "Guinée-Bissao" => 245, "Barbade" => 246, "Ascension" => 247, "Seychelles" => 248, "Soudan" => 249, "Rwanda" => 250, "Ethiopie" => 251, "Somalie" => 252, "Djibouti" => 253, "Kenya" => 254, "Tanzanie" => 255, "Ouganda" => 256, "Burundi" => 257, "Mozambique" => 258, "Zambie" => 260, "Madagascar" => 261, "Réunion" => 262, "Zimbabwe" => 263, "Namibie" => 264, "Malawi" => 265, "Lesotho" => 266, "Botswana" => 267, "Antigua-et-Barbuda" => 268, "Swaziland" => 268, "Mayotte" => 269, "République comorienne" => 269, "Saint Hélène" => 290, "Erythrée" => 291, "Aruba" => 297, "Ile Feroe" => 298, "Groà«nland" => 299, "Iles vierges américaines" => 340, "Iles Caïmans" => 345, "Espagne" => 349, "Gibraltar" => 350, "Portugal" => 351, "Luxembourg" => 352, "Irlande" => 353, "Islande" => 354, "Albanie" => 355, "Malte" => 356, "Chypre" => 357, "Finlande" => 358, "Bulgarie" => 359, "Lituanie" => 370, "Lettonie" => 371, "Estonie" => 372, "Moldavie" => 373, "Arménie" => 374, "Biélorussie" => 375, "Andorre" => 376, "Monaco" => 377, "Saint-Marin" => 378, "Ukraine" => 380, "Yougoslavie" => 381, "Croatie" => 385, "Slovénie" => 386, "Bosnie-Herzégovine" => 387, "Macédoine" => 389, "Italie" => 390, "République Tchèque" => 420, "Slovaquie" => 421, "Liechtenstein" => 423, "Bermudes" => 441, "Grenade" => 473, "Iles Falklands" => 500, "Belize" => 501, "Guatemala" => 502, "Salvador" => 503, "Honduras" => 504, "Nicaragua" => 505, "Costa Rica" => 506, "Panama" => 507, "Haïti" => 509, "Guadeloupe" => 590, "Bolivie" => 591, "Guyane" => 592, "Equateur" => 593, "Guinée Française" => 594, "Paraguay" => 595, "Antilles Françaises" => 596, "Suriname" => 597, "Uruguay" => 598, "Antilles hollandaise" => 599, "Saint Eustache" => 599, "Saint Martin" => 599, "Turks et caicos" => 649, "Monteserrat" => 664, "Saipan" => 670, "Guam" => 671, "Antarctique-Casey" => 672, "Antarctique-Scott" => 672, "Ile de Norfolk" => 672, "Brunei Darussalam" => 673, "Nauru" => 674, "Papouasie - Nouvelle Guinée" => 675, "Tonga" => 676, "Iles Salomon" => 677, "Vanuatu" => 678, "Fidji" => 679, "Palau" => 680, "Wallis et Futuna" => 681, "Iles Cook" => 682, "Niue" => 683, "Samoa Américaines" => 684, "Samoa occidentales" => 685, "Kiribati" => 686, "Nouvelle-Calédonie" => 687, "Tuvalu" => 688, "Polynésie Française" => 689, "Tokelau" => 690, "Micronésie" => 691, "Marshall" => 692, "Sainte-Lucie" => 758, "Dominique" => 767, "Porto Rico" => 787, "République Dominicaine" => 809, "Saint-Vincent-et-les Grenadines" => 809, "Corée du Nord" => 850, "Hong Kong" => 852, "Macao" => 853, "Cambodge" => 855, "Laos" => 856, "Trinité-et-Tobago" => 868, "Saint-Christophe-et-Niévès" => 869, "Atlantique Est" => 871, "Marisat (Atlantique Est)" => 872, "Marisat (Atlantique Ouest)" => 873, "Atlantique Ouest" => 874, "Jamaïque" => 876, "Bangladesh" => 880, "Taiwan" => 886, "Maldives" => 960, "Liban" => 961, "Jordanie" => 962, "Syrie" => 963, "Iraq" => 964, "Koweït" => 965, "Arabie saoudite" => 966, "Yémen" => 967, "Oman" => 968, "Palestine" => 970, "Emirats arabes unis" => 971, "Israà«l" => 972, "Bahreïn" => 973, "Qatar" => 974, "Bhoutan" => 975, "Mongolie" => 976, "Népal" => 977, "Tadjikistan (Rep. du)" => 992, "Turkménistan" => 993, "Azerbaïdjan" => 994, "Géorgie" => 995, "Kirghizistan" => 996, "Bahamas" => 1242, "Barbade" => 1246, "Anguilla" => 1264, "Antigua et Barbuda " => 1268, "Vierges Britanniques (Iles)" => 1284, "Vierges Américaines (Iles)" => 1340, "Cayman (Iles)" => 1345, "Bermudes" => 1441, "Grenade" => 1473, "Turks et Caïcos (Iles)" => 1649, "Montserrat" => 1664, "Sainte-Lucie" => 1758, "Dominique" => 1767, "Saint-Vincent-et-Grenadines" => 1784, "Porto Rico" => 1787, "Hawaï" => 1808, "Dominicaine (Rep.)" => 1809, "Saint-Vincent-et-Grenadines" => 1809, "Trinité-et-Tobago" => 1868, "Saint-Kitts-et-Nevis" => 1869, "Jamaïque" => 1876, "Norfolk (Ile)" => 6723 ];

        $options = '';
        foreach ($countries_codes as $country => $code) {
            $options .= '<option '. ($selected==$code?'selected':'') .' value="'.$code.'">'.$country.' : ' .$code.'</option>';
        }
        return new Markup($options, 'UTF-8');
    }
    
}
