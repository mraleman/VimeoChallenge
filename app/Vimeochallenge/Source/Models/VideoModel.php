<?php namespace Vimeochallenge\Source;

/**
 * VideoModel will return relevant VIDEO information from the Database.
 * getCountries() - Returns list of countries with total users that have
 *                  watched the video along with country's profile url.
 * Extra methods can be added to provide video Profile or other resources.
 */
class VideoModel extends BaseTemplate
{
    /**
     * Connect with Database and establish a DB Handler to use.
     */
    final public function __construct()
    {
        //lets start by connecting to the db
        $conn = new Database;
        $this->_dbh = $conn->connect();
        if(!$this->_dbh){
            $db_response = $conn->getResponse();
            $this->setError($db_response['reason']);
            $this->_dbh = null;
        }
        $conn = null;
    }

    final public function getCountries($id, $page=1,$limit=20)
    {
        if ($this->_response['status']) {
            $sqlStmnt = 'SELECT '
                        .'  count(*) total_watched, '
                        .'  vwl.video_id, '
                        .'  c.country_code '
                        .'FROM videos_watch_log vwl '
                        .'LEFT JOIN users u '
                        .'  ON vwl.user_id = u.user_id '
                        .'LEFT JOIN countries c '
                        .'  ON u.country_id = c.country_id '
                        .'WHERE vwl.video_id = :id '
                        .'GROUP BY c.country_code '
                        .'LIMIT 0,'.$limit;

            $rslt = $this->getResults($sqlStmnt,[':id'=>$id]);
            if ($rslt['status'] && !empty($rslt['result'])) {
                $list = [];
                $rows = $rslt['result'];
                for ($i=0;$i<count($rows);$i++) {
                    $ccode = $rows[$i]['country_code'];
                    $list[] = [
                        'countryCode' => $ccode,
                        'watchedTotal' => $rows[$i]['total_watched'],
                        'href' => self::URL.'/country/'.$ccode
                    ];
                }
                //format data so that it outputs the way we want it
                $this->_response['data'] = [
                    'videoId' => $id,
                    //'total' => '',//not implemented yet
                    'limit' => $limit,
                    'result' => $list,
                    //LOGIC NOT IN PLACE TO HANDLE PAGINATION YET
                    'prevPage' => self::URL.'/video/'.$id.'/countries/page/1',
                    'nextPage' => self::URL.'/video/'.$id.'/countries/page/3'
                ];
                $rows=null;
                $list=null;
            //the sql was good but returned no results
            } elseif ($rslt['status'] && empty($rslt['result'])){
                $this->setError('Invalid Country Code');
            } else {
                $this->setError($rslt['reason']);
            }
            $rslt = null;
        }
    }
}
