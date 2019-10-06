<?php
namespace Search\AsyncJobs;
class Search extends \Core\AsyncJobController{
    /**
     * @ScheduleJob('interval'=>300)
     */
    function generateSearchIndex()
    {
        (new \Search\Search())->generateSearchIndex();
    }
}