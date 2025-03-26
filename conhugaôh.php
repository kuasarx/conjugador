<?php
error_reporting(E_ALL &~ E_NOTICE &~ E_DEPRECATED);
error_reporting(1);
require_once 'vendor/autoload.php';
use Andaluh\AndaluhEpa;

class Conjugador {
    private $verbo;
    private $conjugaciones_es;
    private $conjugaciones_an;
    private $terminacion;
    private $susbtituirPor;
    private $verbomodificado;
    private $url;
    private $response;
    

    public function __construct($verbo) {
        $this->verbo = $verbo;
        $this->partialTranscribtion($this->verbo);
        $this->get_conjugaciones();
    }

    // --------------------------  Methods  --------------------------//
    // change ending to infinitive form (eg. âh -> ar)
    private function partialTranscribtion($verbo){
        $verbo = strtolower($verbo);
        // extraemos los 3 ultimos caracteres (â, ê, î cuentan como 2 en una string)
        $terminacion = substr($verbo, -3);
        $this->terminacion = $terminacion;
        $substitutions = array(
            'âh' => 'ar',
            'êh' => 'er',
            'îh' => 'ir',
        );
        // reemplazar la terminacion
        $verbo_modificado = substr($verbo, 0, -3).$substitutions[$terminacion];
        
        $this->verbomodificado = $verbo_modificado;
        $this->susbtituirPor = $substitutions[$terminacion];
        $this->verbo = $verbo_modificado;
         if ($verbo_modificado == 'cañear'){
            $this->verbo = 'cañearse';
         }
    }
    // translate array
    private function translate_array($arr){
        $result = array();
        $andaluh = new AndaluhEpa();
        foreach($arr as $key => $value){
            $tr = $andaluh->transliterate($value);
            array_push($result, $tr);
        }
        return $result;
    }
    private function get_conjugaciones(){
        $url = "http://192.168.0.84:32771/conjugate/es/".urlencode($this->verbo);
        //$url = urlencode($url);
        $this->url = $url;
        //echo $url; exit;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $this->response = $response;
        curl_close($ch);
        $responseArray = json_decode($response, true);
        $this->conjugaciones_es = $responseArray;
        //print_r($response); exit;
    }

    public function conjugate(){
        // replace array
        $andaluh = new AndaluhEpa();
        $data = $this->conjugaciones_es;
        $vebo_conjugado = array(
            'infinitivo' => $data['value']['moods']['infinitivo']['infinitivo'],
            'indicativo' => $data['value']['moods']['indicativo'],
            'subjuntivo' => $data['value']['moods']['subjuntivo'],
            'imperativo' => $data['value']['moods']['imperativo'],
            'condicional' => $data['value']['moods']['condicional'],
            'gerundio' => $data['value']['moods']['gerundio']['gerundio'],
            'participo' => $data['value']['moods']['participo']['participo'],
        );
        $vebo_conjugado2['es_es'] = array(
            'infinitivo' => $data['value']['moods']['infinitivo']['infinitivo'],
            'gerundio' => $data['value']['moods']['gerundio']['gerundio'],
            'participo' => $data['value']['moods']['participo']['participo']
        );
        $vebo_conjugado2['an_es'] = array(
            $andaluh->transliterate('infinitivo') => $this->translate_array($data['value']['moods']['infinitivo']['infinitivo']),
            $andaluh->transliterate('gerundio') => $this->translate_array($data['value']['moods']['gerundio']['gerundio']),
            $andaluh->transliterate('participo') => $this->translate_array($data['value']['moods']['participo']['participo'])
        );
        foreach($vebo_conjugado as $key => $value){
            // formas verbales: infinitivo, indicativo, etc...
            if($key == 'imperativo' ){
                foreach($value as $k => $v){
                    $contador = 0;
                    foreach($v as $tiempo){
                        $contador ++;
                        switch($contador){
                            case 1:
                                $vebo_conjugado2['es_es'][$key][$k]['tú'] = $tiempo;
                                $vebo_conjugado2['an_es'][$andaluh->transliterate($key)][$andaluh->transliterate($k)][$andaluh->transliterate('tú')] = $andaluh->transliterate($tiempo);
                                break;
                            case 2:
                                $vebo_conjugado2['es_es'][$key][$k]['usted'] = $tiempo;
                                $vebo_conjugado2['an_es'][$andaluh->transliterate($key)][$andaluh->transliterate($k)][$andaluh->transliterate('usted')] = $andaluh->transliterate($tiempo);
                                break;
                            case 3:
                                $vebo_conjugado2['es_es'][$key][$k]['nosotros, nosotras'] = $tiempo;
                                $vebo_conjugado2['an_es'][$andaluh->transliterate($key)][$andaluh->transliterate($k)][$andaluh->transliterate('nosotros / nosotras')] = $andaluh->transliterate($tiempo);
                                break;
                            case 4:
                                $vebo_conjugado2['es_es'][$key][$k]['vosotros, vosotras'] = $tiempo;
                                $vebo_conjugado2['an_es'][$andaluh->transliterate($key)][$andaluh->transliterate($k)][$andaluh->transliterate('vosotros / vosotras')] = $andaluh->transliterate($tiempo);
                                $vebo_conjugado2['an_es'][$andaluh->transliterate($key)][$andaluh->transliterate($k)][$andaluh->transliterate('ustedes')] = $andaluh->transliterate($tiempo);
                                break;
                            case 5:
                                $vebo_conjugado2['es_es'][$key][$k]['ustedes'] = $tiempo;
                                break;
                        }
                    }
                }
                continue;
            }
            /*echo '<pre>';
            print_r($vebo_conjugado2);
            echo '</pre>';
            //exit;*/
            foreach($value as $k => $v){
                // tiepos verbales
                $contador = 0;
                foreach($v as $tiempo){
                    // conjugaciones
                    // split string $tiempo in an array with the first space as delimeter
                    $t = explode(" ", $tiempo, 2);
                    $contador ++;
                    switch($contador){
                        case 1:
                            $vebo_conjugado2['es_es'][$key][$k]['yo'] = $t[1];
                            $vebo_conjugado2['an_es'][$andaluh->transliterate($key)][$andaluh->transliterate($k)][$andaluh->transliterate('yo')] = $andaluh->transliterate($t[1]);
                            break;
                        case 2:
                            $vebo_conjugado2['es_es'][$key][$k]['tú'] = $t[1];
                            $vebo_conjugado2['an_es'][$andaluh->transliterate($key)][$andaluh->transliterate($k)][$andaluh->transliterate('tú')] = $andaluh->transliterate($t[1]);
                            break;
                        case 3:
                            $vebo_conjugado2['es_es'][$key][$k]['usted'] = $t[1];
                            $vebo_conjugado2['an_es'][$andaluh->transliterate($key)][$andaluh->transliterate($k)][$andaluh->transliterate('usted')] = $andaluh->transliterate($t[1]);
                            $vebo_conjugado2['es_es'][$key][$k]['él / ella'] = $t[1];
                            $vebo_conjugado2['an_es'][$andaluh->transliterate($key)][$andaluh->transliterate($k)][$andaluh->transliterate('él / ella')] = $andaluh->transliterate($t[1]);
                            break;
                        case 4:
                            $vebo_conjugado2['es_es'][$key][$k]['nosotros, nosotras'] = $t[1];
                            $vebo_conjugado2['an_es'][$andaluh->transliterate($key)][$andaluh->transliterate($k)][$andaluh->transliterate('nosotros, nosotras')] = $andaluh->transliterate($t[1]);
                            break;
                        case 5:
                            $vebo_conjugado2['an_es'][$andaluh->transliterate($key)][$andaluh->transliterate($k)][$andaluh->transliterate('vosotros, vosotras')] = $andaluh->transliterate($t[1]);
                            $vebo_conjugado2['es_es'][$key][$k]['vosotros / vosotras'] = $t[1];
                            $vebo_conjugado2['an_es'][$andaluh->transliterate($key)][$andaluh->transliterate($k)][$andaluh->transliterate('ustedes')] = $andaluh->transliterate($t[1]);
                            break;
                        case 6:
                            $vebo_conjugado2['es_es'][$key][$k]['ustedes'] = $t[1];
                            $vebo_conjugado2['es_es'][$key][$k]['ellos / ellas'] = $t[1];
                            $vebo_conjugado2['an_es'][$andaluh->transliterate($key)][$andaluh->transliterate($k)][$andaluh->transliterate('ellos, ellas')] = $andaluh->transliterate($t[1]);
                            break;
                    }

                }
            }
        }
        $this->conjugaciones_an = $vebo_conjugado2['an_es'];
        return $this->conjugaciones_an;
    }
}

// Ejemplo de uso

$verboMappings = array(
    '-abahâh' => 'abajâh',
    'abaneçerçe' => 'abaneserçe',
    'abâtteçêh' => 'abastecêh',
    'âbbertîh' => 'advertîh',
    '-abentahâh' => 'aventajâh',
    'abergonçâh' => 'Avergonzâh',
    'abiçâh' => 'Avisâh',
    'abolîh' => 'habolîh',
    'abraçâh' => 'Abrazâh',
    'abroxâh' => 'Abrochâh',
    'abuçâh' => 'Abusâh',
    'aburgeçarçe' => 'aburgesâh',
    'açâh' => 'Asâh',
    'açarâh' => 'azarâh',
    'açararçe' => 'azararçe',
    'açareâh' => 'azareâh',
    'açarearçe' => 'azareâh',
    'açêh' => 'haçêh',
    'açexâh' => 'Açechâh',
    'açîh' => 'asîh',
    'acoçâh' => 'Acosâh',
    'acohêh' => 'Acogêh',
    'açolâh' => 'Asolâh',
    'aconçehâh' => 'Aconçejâh',
    'açorâh' => 'azorâh',
    'açordâh' => 'azordâh',
    'açotâh' => 'Azotâh',
    'acôttâh' => 'Acostâh',
    'acuçâh' => 'Acusâh',
    'açuçâh' => 'Azuzâh',
    'açulâh' => 'azulâh',
    'âdduçîh' => 'abduçîh',
    'adergaçâh' => 'Adergazâh',
    'aderîh' => 'Adherîh',
    'adîh' => 'hadîh',
    'adurîh' => 'hadurîh',
    'afiançâh' => 'Afianzâh',
    'aflihîh' => 'Afligîh',
    'aflohâh' => 'Aflojâh',
    'agaçahâh' => 'Agaçajâh',
    'agaxâh' => 'Agachâh',
    'agihâh' => 'aguijâh',
    'agihoneâh' => 'aguijoneâh',
    'agredîh' => 'agredêh',
    'agudiçâh' => 'Agudizâh',
    'aguebâh' => 'agüebâh',
    'aguebonâh' => 'agüebonâh',
    'aguebonearçe' => 'agüebonearçe',
    'âhhetibâh' => 'adjetibâh',
    'âhhudicâh' => 'Adjudicâh',
    'âhhuntâh' => 'Adjuntâh',
    'âhhurâh' => 'Adjurâh',
    '-ahiçâh' => 'ajiçâh',
    'ahiliçâh' => 'agilizâh',
    '-ahitâh' => 'Agitâh',
    'ahônnalâh' => 'ajornalâh',
    'ahorâh' => 'ajorâh',
    '-ahorrâh' => 'ajorrâh',
    'ahûttâh' => 'Ajustâh',
    'ahûttiçiâh' => 'ajûstiçiâh',
    'aliçâh' => 'Alisâh',
    'aloqueçêh' => 'aloquecêh',
    'amaçâh' => 'Amasâh',
    'amanâh' => 'hamanâh',
    'amobêh' => 'amovêh',
    'anêççâh' => 'Anexâh',
    'anoxeçêh' => 'anochecêh',
    'antolohiçâh' => 'antolojizâh',
    'añehâh' => 'hañejâh',
    'añudâh' => 'hañudâh',
    'apunarçe' => 'hapunarçe',
    'armorçâh' => 'almorzâh',
    'arrebentâh' => 'arreventâh',
    'arreborbêh' => 'arrevolvêh',
    'arreçîh' => 'arrecêh',
    'atrabeçâh' => 'Atraveçâh',
    'âttreñîh' => 'astreñîh',
    'axabacanâh' => 'achabacanâh',
    'axacâh' => 'achacâh',
    'axaflanâh' => 'achaflanâh',
    'axâh' => 'achâh',
    'barbuçîh' => 'balbusîh',
    'bêh' => 'vêh',
    'bêttîh' => 'Vestîh',
    'biahâh' => 'Viajâh',
    'bihilâh' => 'Bigilâh',
    'borbêh' => 'volvêh',
    'bruhîh' => 'brujîh',
    'buyîh' => 'bullîh',
    'çabêh' => 'sabêh',
    'çableâh' => 'sableâh',
    'çaçeâh' => 'zazeâh',
    'çaneâh' => 'Saneâh',
    'çanhuaneâh' => 'sanjuaneâh',
    'cañeâh' => 'kañear',
    'çapâh' => 'zapâh',
    'çapeâh' => 'Zapeâh',
    'çardâh' => 'Saldâh',
    'çargâh' => 'sargâh',
    'çarteâh' => 'Salteâh',
    'çaxâh' => 'saxâh',
    'çeduçîh' => 'Seduçîh',
    'çêh' => 'Sêh',
    'çembrâh' => 'Sembrâh',
    'çênnêh' => 'zesnêh',
    'çênnîh' => 'cernîh',
    'çênnîh' => 'zesnîh',
    'çeñâh' => 'señâh',
    'çerbîh' => 'Servîh',
    'çihilâh' => 'çijilâh',
    'çobihâh' => 'zobihâh',
    'çobrâh' => 'sobrâh',
    'çobrebertêh' => 'zobrevertêh',
    'çobreberterçe' => 'zobreverterçe',
    'çobrebêttîh' => 'sobrevestîh',
    'çobrebolâh' => 'Sobrevolâh',
    'çobrehirâh' => 'çobrejirâh',
    'çocâh' => 'socâh',
    'coçêh' => 'Cocêh',
    'codirihîh' => 'codirijîh',
    'cohêh' => 'Cojêh',
    'cohexâh' => 'cojexâh',
    'çohûggâh' => 'sojuzgâh',
    'çolâh' => 'solâh',
    'çoleâh' => 'soleâh',
    'colehîh' => 'colejîh',
    'colorîh' => 'colorâh',
    'comberhîh' => 'comberjîh',
    'compadeçêh' => 'Compadecêh',
    'compahinâh' => 'Compaginâh',
    'compareçêh' => 'Comparecêh',
    'compelîh' => 'compelêh',
    'complaçêh' => 'Complacêh',
    'compunhîh' => 'compunjîh',
    'çonâh' => 'Sonâh',
    'conçegîh' => 'Conseguîh',
    'conçênnîh' => 'koncernîh',
    'condoleçerçe' => 'condolecerçe',
    'conflihîh' => 'konflijîh',
    'conhelâh' => 'Congelâh',
    'conheniâh' => 'Congeniâh',
    'conhêttionâh' => 'conjestionâh',
    'conheturâh' => 'Conjeturâh',
    'conhugâh' => 'Conjugâh',
    'conhuntâh' => 'conjuntâh',
    'conhurâh' => 'Conjurâh',
    'conhuramentâh' => 'conjuramentâh',
    'conhuramentarçe' => 'conjuramentarçe',
    'çônniheâh' => 'çônnijeâh',
    'conoçêh' => 'Conocêh',
    'çonreîh' => 'Sonreîh',
    'contahiâh' => 'Contagiâh',
    'contorçerçe' => 'contorcerçe',
    'contra\'rgumentâh' => 'contra\'rgumentâh',
    'contraaçêh' => 'contrahaçêh',
    'çoñâh' => 'Soñâh',
    'çopâh' => 'zopâh',
    'çopeâh' => 'zopeâh',
    'çoqueteâh' => 'zoqueteâh',
    'corcuçîh' => 'corcucîh',
    'corgâh' => 'Colgâh',
    'correhîh' => 'Corregîh',
    'çortâh' => 'Soltâh',
    'cosêh' => 'Cosêh',
    'cotehâh' => 'Cotejâh',
    'côttâh' => 'Costâh',
    'côttreñîh' => 'constreñîh',
    'cuahâh' => 'Cuajâh',
    'cuçîh' => 'cucîh',
    'çuherîh' => 'Çujerîh',
    'çuhetâh' => 'Çujetâh',
    'çuhêttionâh' => 'sujestionâh',
    'çurrâh' => 'zurrâh',
    'çurrarçe' => 'zurrarçe',
    'çurrearçe' => 'zurrearçe',
    'çurtîh' => 'surtîh',
    'dêbbeçerrâh' => 'desbecerrâh',
    'deçaçîh' => 'desasîh',
    'deçapareçêh' => 'desaparecêh',
    'dêççeñîh' => 'desceñîh',
    'dêccohonarçe' => 'dêccojonarçe',
    'dêccolorîh' => 'descolorâh',
    'dêcconhêttionâh' => 'dêcconjestionâh',
    'deçêmmoeçêh' => 'deçêmmoecêh',
    'deçenhaeçâh' => 'deçenjaeçâh',
    'deçenharmâh' => 'deçenjarmâh',
    'deçenhaulâh' => 'deçenjaulâh',
    'deçexiçâh' => 'desechizâh',
    'deçobedeçêh' => 'deçobedecêh',
    'deçôççihenâh' => 'deçôççijenâh',
    'deçumedeçêh' => 'deshumedecêh',
    'deçunçîh' => 'desunçîh',
    'dêddeçîh' => 'desdeçîh',
    'dêddibuhâh' => 'dêddibujâh',
    'dêddibuharçe' => 'dêddibujarçe',
    'dêffayeçêh' => 'dêffayecêh',
    'dêffrunçîh' => 'desfrunçîh',
    'dêggânnatarçe' => 'desgaznatarçe',
    'dêgginçâh' => 'desginzâh',
    'dehenerâh' => 'dejenerâh',
    'dêhharretâh' => 'desjarretâh',
    'dêl-lehitimâh' => 'dêl-lejitimâh',
    'dêl-lexugiyâh' => 'dêl-lechugiyâh',
    'dêl-luçîh' => 'deslucîh',
    'dêmmehorâh' => 'dêmmejorâh',
    'dêpprotehêh' => 'dêpprotejêh',
    'derexiçâh' => 'derechizâh',
    'dêrrabâh' => 'desrabâh',
    'dêrramâh' => 'desramâh',
    'dêrrengâh' => 'desrengâh',
    'dêtteñîh' => 'desteñîh',
    'dêttoçerçe' => 'destoserçe',
    'donhuaneâh' => 'donjuaneâh',
    'êccabuyîh' => 'escabuyîh',
    'edêh' => 'hedêh',
    'ehabrîh' => 'ejabrîh',
    'ehecutâh' => 'ejecutâh',
    'eherçêh' => 'ejercêh',
    'embeyeçêh' => 'embeyecêh',
    'embôqqueçêh' => 'embôqquecêh',
    'emboxinxâh' => 'embochinchâh',
    'êmmariyeçerçe' => 'esmariyeçerçe',
    'êmmoyeçêh' => 'esmoyecêh',
    'empaboreçêh' => 'empaborecêh',
    'empalideçêh' => 'empalidecêh',
    'empedênnîh' => '%empedeznîh',
    'empedênnîh' => 'empedeznîh',
    'empeyêh' => 'empellêh',
    'encanâh' => '%encanâh',
    'encanarçe' => 'encanar',
    'ençañâh' => 'ensañâh',
    'ençartâh' => 'ensartâh',
    'encayeçêh' => 'encalleçêh',
    'encloqueçêh' => 'encloquecêh',
    'ençobrâh' => 'enzobrâh',
    'ençoñâh' => 'ensoñâh',
    'encoñar' => 'enkoñar',
    'encoñarçe' => 'enkoñarçe',
    'ençordâh' => 'ensordâh',
    'encrueleçêh' => 'encruelecêh',
    'enerhiçâh' => 'enerjiçâh',
    'enexiçâh' => 'enechizâh',
    'enferboreçêh' => 'enferborecêh',
    'enfiereçerçe' => 'enfierecerçe',
    'enflaqueçêh' => 'enflaquecêh',
    'enfranqueçêh' => 'enfranquecêh',
    'enfuçîh' => 'enfucîh',
    'engerâh' => 'hengerâh',
    'engiharrâh' => 'engijarrâh',
    'enrihideçêh' => 'enrijidecêh',
    'enroheçêh' => 'enrojecêh',
    'enronâh' => 'henronâh',
    'enronqueçêh' => 'enronquecêh',
    'entayeçêh' => 'entayecêh',
    'entoñâh' => 'hentoñâh',
    'entuyeçêh' => 'entuyecêh',
    'enxîh' => 'enchîh',
    'enyenteçêh' => 'enyentecêh',
    'eñîh' => 'heñîh',
    'êppadañâh' => 'espadañâh',
    'êpparçîh' => 'esparzîh',
    'erbîh' => 'hervîh',
    'erîh' => 'herîh',
    'êttâh' => 'Estâh',
    'êttarçîh' => 'êttarcîh',
    'êttreñîh' => 'estreñîh',
    'exiçâh' => 'echizâh',
    'garantîh' => 'garantizâh',
    'inçurhîh' => 'insurjîh',
    'indihêttâh' => 'indijestâh',
    'indihêttarçe' => 'indijestarçe',
    'inxîh' => 'hinxîh',
    'jâppeâh' => 'jaspeâh',
    'jâttarçe' => 'jactarçe',
    'jêttâh' => 'jectâh',
    'jêtteâh' => 'jesteâh',
    'jêtticulâh' => 'jesticulâh',
    'jêttionâh' => 'jestionâh',
    'jônnalâh' => 'jornalâh',
    'jônnaleâh' => 'jornaleâh',
    'jûggâh' => 'juzgâh',
    'junçîh' => 'juncîh',
    'jûttâh' => 'justâh',
    'jûttiçiâh' => 'justiçiâh',
    'jûttificâh' => 'justificâh',
    'jûttipreçiâh' => 'justipreçiâh',
    'lehîl-lâh' => 'lejislâh',
    'lentehuelâh' => 'lentejuelâh',
    'lobregeçêh' => 'lobregecêh',
    'luhuriâh' => 'lujuriâh',
    'manîh' => '%manîh',
    'mardeçîh' => 'Maldecîh',
    'marhinâh' => 'marjinâh',
    'marparîh' => 'malparîh',
    'mehêh' => 'mejêh',
    'mobêh' => 'movêh',
    'muçîh' => 'mucîh',
    'muçirçe' => 'mucirçe',
    'muyîh' => 'mullîh',
    'oheçêh' => 'ohecêh',
    'ôhhetâh' => 'objetâh',
    'ôhhetibâh' => 'objetibâh',
    'omoheneiçâh' => 'omojeneiçâh',
    'paçâh' => 'pasar',
    'paçâh' => 'pazâh',
    'paeçêh' => 'Paecêh',
    'perhudicâh' => 'perjudicâh',
    'piçoteâh' => 'pisoteâh',
    'pimpoyeçêh' => 'pimpoyecêh',
    'poêh' => 'Podêh',
    'preçumîh' => 'presumîh',
    'preêççîttîh' => 'preexîstîh',
    'prehubilâh' => 'prejubilâh',
    'prehûggâh' => 'prejuzgâh',
    'reabâtteçêh' => 'reabâttecêh',
    'reaçumîh' => 'reasumîh',
    'reçabêh' => 'resabêh',
    'reçarçîh' => 'resarçîh',
    'reçobrâh' => 'rezobrâh',
    'rehîttrâh' => 'rejistrâh',
    'rehubeneçêh' => 'rejubenecêh',
    'remuyîh' => 'remullîh',
    'retayeçêh' => 'retayecêh',
    'retrâmmitîh' => 'retransmitîh',
    'tarhâh' => 'tarjâh',
    'tarheteâh' => 'tarjeteâh',
    'tarhetearçe' => 'tarjetearçe',
    'trâl-luçîh' => 'trasluçîh',
    'trâl-luçirçe' => '%translucîh',
    'tuyîh' => 'tullîh',
    'xâl-lâh' => 'charlâh',
    'xapoteâh' => 'chapoteâh',
    'xâqqueâh' => 'chasqueâh',
    'xîmmorreâh' => 'chismorreâh',
    'xîmmorreâh' => 'chismorreâh',
    'xocâh' => 'chocâh',
    'yaçêh' => 'yacêh',
    'yobêh' => 'llovêh',
    
);
function replaceEnding($string) {
    $string = str_replace("çobre", "sobre", $string);
    $string = str_replace("gui", "güi", $string);
    $string = str_replace("gue", "güe", $string);
    $string = str_replace("ge", "gue", $string);
    $string = str_replace("gîh", "guîh", $string);
    $string = str_replace("gi", "gui", $string);
    $string = str_replace("aham", "ajam", $string);
    $string = str_replace("ahar", "ajar", $string);
    $string = str_replace("ahen", "ajen", $string);
    $string = str_replace("ihad", "ijad", $string);
    $string = str_replace("ihie", "ijie", $string);
    $string = str_replace("ihue", "ijue", $string);
    $string = str_replace("arhen", "arjen", $string);
    $string = str_replace("arho", "arjo", $string);
    $string = str_replace("anhe", "anje", $string);
    $string = str_replace("aher", "ajer", $string);
    $string = str_replace("ahet", "ajet", $string);
    $string = str_replace("ahi", "aji", $string);
    $string = str_replace("aho", "ajo", $string);
    $string = str_replace("ahu", "aju", $string);
    $string = str_replace("ehi", "eji", $string);
    $string = str_replace("eho", "ejo", $string);
    $string = str_replace("ihi", "iji", $string);
    $string = str_replace("ihe", "ije", $string);
    $string = str_replace("oha", "oja", $string);
    $string = str_replace("ohe", "oje", $string);
    $string = str_replace("ohi", "oji", $string);
    $string = str_replace("oho", "ojo", $string);
    $string = str_replace("ohu", "oju", $string);
    $string = str_replace("uham", "ujam", $string);
    $string = str_replace("axixa", "achicha", $string);
    $string = str_replace("uxaxe", "uchache", $string);
    $string = str_replace("oxil", "ochil", $string);
    $string = str_replace("axixi", "achichi", $string);
    $string = str_replace("axa", "acha", $string);
    $string = str_replace("axe", "ache", $string);
    $string = str_replace("axi", "achi", $string);
    $string = str_replace("axo", "acho", $string);
    $string = str_replace("oxa", "ocha", $string);
    $string = str_replace("oxe", "oche", $string);
    $string = str_replace("oxi", "ochi", $string);
    $string = str_replace("exa", "echa", $string);
    $string = str_replace("exe", "eche", $string);
    $string = str_replace("exi", "echi", $string);
    $string = str_replace("exo", "echo", $string);
    $string = str_replace("exu", "echu", $string);
    $string = str_replace("ixa", "icha", $string);
    $string = str_replace("ixe", "iche", $string);
    $string = str_replace("ixi", "ichi", $string);
    $string = str_replace("ixo", "icho", $string);
    $string = str_replace("ixu", "ichu", $string);
    $string = str_replace("uxa", "ucha", $string);
    $string = str_replace("uxe", "uche", $string);
    $string = str_replace("uxi", "uchi", $string);
    $string = str_replace("uxo", "ucho", $string);
    $string = str_replace("uxu", "uchu", $string);
    $string = str_replace("axuxa", "achucha", $string);
    $string = str_replace("axu", "achu", $string);
    $string = str_replace("ruhu", "ruju", $string);
    $string = str_replace("dêhh", "desj", $string);
    $string = str_replace("ehe", "eje", $string);
    $string = str_replace("nh", "nj", $string);
    $string = str_replace("rhi", "rji", $string);
    $string = str_replace("rhe", "rje", $string);
    
    $endings = [
        'ahaheâh' => 'ajajeâh',
        'ohiçâh' => 'ojizâh',
        'forçâh' => 'forzâh',
        'corçâh' => 'corzâh',
        'morçâh' => 'morzâh',
        'mençâh' => 'menzâh',
        'ççênnîh' => 'scêrnîh',
        'çênnîh' => 'cernîh',
        'çâh' => 'sâh',
        'ereçêh' => 'erecêh',
        'çêh' => 'sêh',
        'exuxâh' => 'echuchâh',
        'xâh' => 'châh',
        'ahâh' => 'ajâh',
        'aheâh' => 'ajeâh',
        'ahadâh' => 'ajadâh',
        'ahanâh' => 'ajanâh',
        'ohâh' => 'ojâh',
        'eheâh' => 'ejeâh',
        'ehâh' => 'ejâh',
        'eçerçe' => 'eserçe',
        'ehacâh' => 'ejacâh',
        'eherâh' => 'ejerâh',
        'ehentâh' => 'egentâh',
        'ehearçe' => 'ejearçe',
        'ehigâh' => 'egigâh',
        'ehoneâh' => 'ejoneâh',
        'ehorreâh' => 'ejorreâh',
        'erheâh' => 'erjeâh',
        'erhâh' => 'erjâh',
        'erhêh' => 'erjêh',
        'ihiâh' => 'ijiâh',
        'ehiâh' => 'ejiâh',
        'iehâh' => 'iejâh',
        'inhîh' => 'injîh',
        'ixarçe' => 'icharçe',
        'açarçe' => 'asarçe',
        'orçarçe' => 'orsarçe',
        'oharçe' => 'ojarçe',
        'raharçe' => 'rajarçe',
        'uhâh' => 'ujâh',
        'urçîh' => 'urcîh',
        'çarçe' => 'sarçe',
        'uhardâh' => 'ujardâh',
        'ruhâh' => 'rujâh',
        'uxixiâh' => 'uchichiâh',
        'uxixeâh' => 'uchicheâh',
        'uxuxeâh' => 'uchucheâh',
        'xeâh' => 'cheâh',
        'xambrâh' => 'chambrâh',
        'xarçe' => 'charçe',
        'xaparçe' => 'chaparçe',
        'xetâh' => 'chetâh',
        'xeteâh' => 'cheteâh',
        'xinâh' => 'chinâh',
        'xinarçe' => 'chinarçe',
        'xorrarçe' => 'chorrarçe',
        'xiyâh' => 'chiyâh',
        'xugâh' => 'chugâh',
        'xurrâh' => 'churrâh',
        'obihâh' => 'obijâh',
        'ohêh' => 'ojêh',
        'oheâh' => 'ojeâh',
        'ohinâh' => 'ojinâh',
        'ohonâh' => 'ojonâh',
        'ohamâh' => 'ojamâh',
        'ohelâh' => 'ojelâh',
        'ihâh' => 'ijâh',
        'iharâh' => 'ijarâh',
        'iholâh' => 'ijolâh',
        'ihoneâh' => 'ijoneâh',
        'rigîh' => 'riguîh',
        'guecâh' => 'huecâh',
        'gueçarçe' => 'hueçarçe',
        'guenâh' => 'huenâh',
        'geâh' => 'gueâh',
        'uherâh' => 'ujerâh',
        'uhereâh' => 'ujereâh',
        'ahûttâh' => 'ajustâh',
        'ehetâh' => 'ejetâh',
        'ehuqueâh' => 'ejuqueâh',
        'uheâh' => 'ujeâh',
        'ahelâh' => 'ajelâh',
        'çalâh' => 'salâh',
        'çalificâh' => 'salificâh',
        'çaminâh' => 'zaminâh',
        'çampâh' => 'zampâh',
        'çampeâh' => 'zampeâh',
        'çancaheâh' => 'zancaheâh',
        'çangificâh' => 'çanguificâh',
        'anhâh' => 'anjâh',
        'inhentâh' => 'injentâh',
        'uxareâh' => 'uchareâh',
        'uxareteâh' => 'uchareteâh',
        'forhâh' => 'forjâh',
        'iherâh' => 'ijerâh',
        'tehêh' => 'tejêh',
        'erhîh' => 'erjîh',
        'iherîh' => 'ijerîh',
        'ihitâh' => 'ijitâh',
        'ihençiâh' => 'ijençiâh',
        'ihîh' => 'ijîh',
        'ohitâh' => 'ojitâh',
        'oxerâh' => 'ocherâh',
        'ehîh' => 'ejîh',
        'iharçe' => 'ijarçe',
        'ehucâh' => 'ejucâh',
        'uharçe' => 'ujarçe',
        'ehilâh' => 'ejilâh',
        'eharçe' => 'ejarçe',
        'ehonâh' => 'ejonâh',
        'nhabâh' => 'njabâh',
        'ehuntâh' => 'ejuntâh',
        'exocâh' => 'echocâh',
        'rheâh' => 'rjeâh',
        'uhîh' => 'ujîh',
        'iheâh' => 'ijeâh',
        'ehitâh' => 'ejitâh',
        'rhitâh' => 'rjitâh',
        'ahaçeâh' => 'ajaçeâh',
        'ahadeâh' => 'ajadeâh',
        'ahadereâh' => 'ajadereâh',
        'xoneâh' => 'choneâh',
        'ihenâh' => 'ijenâh',
        'ehalâh' => 'ejalâh',
        'ohalâh' => 'ojalâh',
        'erhurâh' => 'erjurâh',
        'erheñâh' => 'erjeñâh',
        'humbrâh' => 'jumbrâh',
        'ehîh' => 'egîh',
        'urhîh' => 'urjîh',
        'uhiâh' => 'ujiâh',
        'ehêh' => 'ejêh',
        'mobêh' => 'movêh',
        //'cjalâh' => 'chalâh',
        
        
        //'cañeâh' => 'cañearçe',
    ];
    //echo $string; exit;
    
    foreach ($endings as $ending => $replacement) {
        if (substr($string, -strlen($ending)) === $ending) {
            $string = substr_replace($string, $replacement, -strlen($ending));
            break; // Exit the loop after the first replacement
        }
    }
    
    
    //echo $string; exit;

    return $string;
}

function replaceFirst_H_Letter($string) {
  if (substr($string, 0, 1) == 'h') {
    $string = 'j' . substr($string, 1);
  }
  return $string;
}
function replaceFirst_X_Letter($string) {
  if (substr($string, 0, 1) == 'x') {
    $string = 'ch' . substr($string, 1);
  }
  return $string;
}

function replaceUpperCase_accent($string) {
    $search = array('Â', 'Ê', 'Î', 'Ô', 'Û', 'Ç');
    $replace = array('â', 'ê', 'î', 'ô', 'û', 'ç');
    $result = str_replace($search, $replace, $string);
    return $result;
}
function replaceLetterK(&$array) {
    foreach ($array as &$element) {
        if (is_array($element)) {
            replaceLetterK($element);  // Recursive call for nested arrays
        } elseif (is_string($element)) {
            $element = str_replace('k', 'c', $element);
        }
    }
}

function replacePercentSymbol(&$array) {
    foreach ($array as &$element) {
        if (is_array($element)) {
            replacePercentSymbol($element);  // Recursive call for nested arrays
        } elseif (is_string($element)) {
            $element = str_replace('%', '', $element);
        }
    }
}
$requestQ = strtolower($_REQUEST['q']);
$requestQ = replaceFirst_X_Letter($requestQ);
//echo $requestQ; exit;
$requestQ = replaceFirst_H_Letter(replaceUpperCase_accent($requestQ));
//echo $requestQ; exit;
$verbo = isset($verboMappings[$requestQ]) ? $verboMappings[$requestQ] : $requestQ;
//echo $verbo; exit;

$verbo = replaceEnding($verbo);
//echo $verbo; exit;

$conjugador = new Conjugador($verbo);
$conjugaciones = $conjugador->conjugate();
if($verbo == 'koncernîh' || $verbo == 'konflijîh' || $verbo == 'enkoñar' || $verbo == 'enkoñarçe' || $verbo == 'kañeâh'){
    replaceLetterK($conjugaciones);
}
if($verbo == '%manîh' || $verbo == '%encanâh' || $verbo == '%empedeznîh' || $verbo == '%translucîh'){
    replacePercentSymbol($conjugaciones);
}
if(strtolower($verbo) == 'aferrâh'){
    $conjugaciones['indicatibo']['preçente']['yo'] = "aferro";
    $conjugaciones['indicatibo']['preçente']['tú'] = "aferrâ";
    $conjugaciones['indicatibo']['preçente']['ûtté'] = "aferrâ";
    $conjugaciones['indicatibo']['preçente']['él / eya'] = "aferra";
    $conjugaciones['indicatibo']['preçente']['eyô, eyâ'] = "aferran";
    
    $conjugaciones['çûhhuntibo']['preçente']['yo'] = "aferre";
    $conjugaciones['çûhhuntibo']['preçente']['tú'] = "aferrê";
    $conjugaciones['çûhhuntibo']['preçente']['ûtté'] = "aferre";
    $conjugaciones['çûhhuntibo']['preçente']['él / eya'] = "aferre";
    $conjugaciones['çûhhuntibo']['preçente']['eyô, eyâ'] = "aferren";
}
if(strtolower($verbo) == 'aterrâh'){
    $conjugaciones['indicatibo']['preçente']['yo'] = "aterro";
    $conjugaciones['indicatibo']['preçente']['tú'] = "aterrâ";
    $conjugaciones['indicatibo']['preçente']['ûtté'] = "aterra";
    $conjugaciones['indicatibo']['preçente']['él / eya'] = "aterra";
    $conjugaciones['indicatibo']['preçente']['eyô, eyâ'] = "aterran";
    
    $conjugaciones['çûhhuntibo']['preçente']['yo'] = "aterre";
    $conjugaciones['çûhhuntibo']['preçente']['tú'] = "aterrê";
    $conjugaciones['çûhhuntibo']['preçente']['ûtté'] = "aferre";
    $conjugaciones['çûhhuntibo']['preçente']['él / eya'] = "aterre";
    $conjugaciones['çûhhuntibo']['preçente']['eyô, eyâ'] = "aterren";
    
    $conjugaciones['imperatibo']['afirmatibo']['tú'] = "aterra";
    $conjugaciones['imperatibo']['afirmatibo']['ûtté'] = "aterre";
    $conjugaciones['imperatibo']['negatibo']['tú'] = "aterre";
    $conjugaciones['imperatibo']['negatibo']['ûtté'] = "aterre";
}
if(strtolower($verbo) == 'peîh'){
    $conjugaciones['indicatibo']['preçente']['yo'] = "pío";
    $conjugaciones['indicatibo']['preçente']['tú'] = "píê";
    $conjugaciones['indicatibo']['preçente']['ûtté'] = "píe";
    $conjugaciones['indicatibo']['preçente']['él / eya'] = "píe";
    $conjugaciones['indicatibo']['preçente']['eyô, eyâ'] = "píen";
    
    $conjugaciones['çûhhuntibo']['preçente']['yo'] = "pía";
    $conjugaciones['çûhhuntibo']['preçente']['tú'] = "píâ";
    $conjugaciones['çûhhuntibo']['preçente']['ûtté'] = "pía";
    $conjugaciones['çûhhuntibo']['preçente']['él / eya'] = "pía";
    $conjugaciones['çûhhuntibo']['preçente']['eyô, eyâ'] = "pían";
    
    $conjugaciones['çûhhuntibo']['pretérito-imperfêtto-1']['yo'] = "piera";
    $conjugaciones['çûhhuntibo']['pretérito-imperfêtto-1']['tú'] = "pierâ";
    $conjugaciones['çûhhuntibo']['pretérito-imperfêtto-1']['ûtté'] = "piera";
    $conjugaciones['çûhhuntibo']['pretérito-imperfêtto-1']['él / eya'] = "piera";
    $conjugaciones['çûhhuntibo']['pretérito-imperfêtto-1']['noçotrô, noçotrâ'] = "piéramô";
    $conjugaciones['çûhhuntibo']['pretérito-imperfêtto-1']['boçotrô, boçotrâ'] = "pieraî";
    $conjugaciones['çûhhuntibo']['pretérito-imperfêtto-1']['ûttedê'] = "pieraî";
    $conjugaciones['çûhhuntibo']['pretérito-imperfêtto-1']['eyô, eyâ'] = "pieran";
    
    $conjugaciones['çûhhuntibo']['pretérito-imperfêtto-2']['yo'] = "pieçe";
    $conjugaciones['çûhhuntibo']['pretérito-imperfêtto-2']['tú'] = "pieçê";
    $conjugaciones['çûhhuntibo']['pretérito-imperfêtto-2']['ûtté'] = "pieçe";
    $conjugaciones['çûhhuntibo']['pretérito-imperfêtto-2']['él / eya'] = "pieçe";
    $conjugaciones['çûhhuntibo']['pretérito-imperfêtto-2']['noçotrô, noçotrâ'] = "piéçemô";
    $conjugaciones['çûhhuntibo']['pretérito-imperfêtto-2']['boçotrô, boçotrâ'] = "pieçeî";
    $conjugaciones['çûhhuntibo']['pretérito-imperfêtto-2']['ûttedê'] = "pieçeî";
    $conjugaciones['çûhhuntibo']['pretérito-imperfêtto-2']['eyô, eyâ'] = "pieçen";
    
    $conjugaciones['çûhhuntibo']['futuro']['yo'] = "piere";
    $conjugaciones['çûhhuntibo']['futuro']['tú'] = "pierê";
    $conjugaciones['çûhhuntibo']['futuro']['ûtté'] = "piere";
    $conjugaciones['çûhhuntibo']['futuro']['él / eya'] = "piere";
    $conjugaciones['çûhhuntibo']['futuro']['noçotrô, noçotrâ'] = "piéremô";
    $conjugaciones['çûhhuntibo']['futuro']['boçotrô, boçotrâ'] = "piereî";
    $conjugaciones['çûhhuntibo']['futuro']['ûttedê'] = "piereî";
    $conjugaciones['çûhhuntibo']['futuro']['eyô, eyâ'] = "pieren";
}
if($_REQUEST['t'] && $_REQUEST['m']){
    $singular = array(); 
    if($_REQUEST['m'] == 'infinitibo'){
        $singular = $conjugaciones['infinitibo'][0]; 
        //print_r(json_encode($singular));
        echo '<ul><li>'.$singular.'</li></ul>';
        exit;
    }
    // obtener el tiempo y el modo verbal especifico
    foreach($conjugaciones[$_REQUEST['m']][$_REQUEST['t']] as $key => $value){
        $singular[][$_REQUEST['t']] = $value; 
    }
    //print_r(json_encode($conjugaciones[$_REQUEST['m']][$_REQUEST['t']]));
    header('Content-Type: application/json; charset=utf-8');
    print_r(json_encode($singular));

}
else{
    header('Content-Type: application/json; charset=utf-8');
    print_r(json_encode($conjugaciones));
}
