<?php
$root = realpath( dirname( __FILE__ ) . '/../../' );
include_once $root . "/inc/Bootstrap.php";
Bootstrap::start();
require_once INIT::$MODEL_ROOT . '/queries.php';

use TaskRunner\Commons\AbstractDaemon;

/**
 * Created by PhpStorm.
 * User: roberto
 * Date: 21/09/15
 * Time: 16.06
 */
class LanguageStatsRunner extends AbstractDaemon {

    public function __construct() {
        parent::__construct();
        Log::$fileName   = "languageStats.log";
        self::$sleepTime = 10; //60 * 60 * 24 * 30 * 1;
    }

    function main( $args = null ) {
        $db = Database::obtain();
        $lsDao = new LanguageStats_LanguageStatsDAO( Database::obtain() );

        do {
            //TODO: create DAO for this
            $today     = date( "Y-m-d" );
            $queryJobs = "SELECT
                        source,
                        target,
                        sum( total_time_to_edit ) as total_time_to_edit,
                        sum( total_raw_wc ) as total_words,
                        sum( COALESCE ( avg_post_editing_effort, 0) ) / sum( coalesce( total_raw_wc, 1) ) as total_post_editing_effort,
                        count(*) as job_count
                      FROM
                        jobs j
                      WHERE
                        completed = 1
                        AND source = '%s'
                      GROUP BY target";

            $langsObj = Langs_Languages::getInstance();

            //getlanguage list
            $languages = $langsObj->getEnabledLanguages();
            $languages = Utils::array_column( $languages, 'code' );

            foreach ( $languages as $source_language ) {
                Log::doLog( "Current source_language: $source_language" );
                echo "Current source_language: $source_language\n";

                $languageStats = $db->fetch_array(
                        sprintf(
                                $queryJobs,
                                $source_language
                        )
                );

                $languageTuples = array();

                foreach ( $languageStats as $languageCoupleStat ) {

                    if ( !self::isLanguageStatValid( $languageCoupleStat ) ) {
                        continue;
                    }

                    Log::doLog("Current language couple: " . $source_language . "-" . $languageCoupleStat['target']);
                    echo "Current language couple: " . $source_language . "-" . $languageCoupleStat['target'] . "\n";

                    $langStatsStruct                            = new LanguageStats_LanguageStatsStruct();
                    $langStatsStruct->date                      = $today;
                    $langStatsStruct->source                    = $languageCoupleStat[ 'source' ];
                    $langStatsStruct->target                    = $languageCoupleStat[ 'target' ];
                    $langStatsStruct->total_word_count          = round( $languageCoupleStat[ 'total_words' ], 4 );
                    $langStatsStruct->total_post_editing_effort = round( $languageCoupleStat[ 'total_post_editing_effort' ], 4 );
                    $langStatsStruct->total_time_to_edit        = round( $languageCoupleStat[ 'total_time_to_edit' ], 4 );
                    $langStatsStruct->job_count                 = $languageCoupleStat[ 'job_count' ];

                    $languageTuples[] = $langStatsStruct;
                }

                //if there is some data for this language couple, insert it
                if ( count( $languageTuples ) > 0 ) {
                    Log::doLog( "Found some stats. Saving in DB.." );
                    echo "Found some stats. Saving in DB..\n";

                    $result = $lsDao->createList( $languageTuples );
                    if(is_null( $result) ){
                        echo "ERROR: DAO failed to insert rows";
                    }
                }

                usleep( 100 );
            }

            Log::doLog( "Everything completed. I can die." );
            echo "Everything completed. I can die.\n";

            //for the moment, this daemon is single-loop-execution
            $this->RUNNING = false;

            if ( $this->RUNNING ) {
                sleep( self::$sleepTime );
            }

        } while ( $this->RUNNING );
    }

    public static function cleanShutDown() {
        // TODO: Implement cleanShutDown() method.
    }

    /**
     * Every cycle reload and update Daemon configuration.
     * @return void
     */
    protected function _updateConfiguration() {
        // TODO: Implement _updateConfiguration() method.
    }

    private static function isLanguageStatValid( $languageStat ) {
        return (
                $languageStat[ 'source' ] != $languageStat[ 'target' ] &&
                preg_match( "#[a-z]-[a-zA-Z-]{2,}#", $languageStat[ 'source' ] ) &&
                preg_match( "#[a-z]-[a-zA-Z-]{2,}#", $languageStat[ 'target' ] )
        );
    }
}

$lsr = LanguageStatsRunner::getInstance();

/**
 * @var $lsr LanguageStatsRunner
 */
$lsr->main( null );
