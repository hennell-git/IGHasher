<?php

namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;
use InstagramScraper\Instagram;
use App\HashParser;
use App\HashSorter;
use App\Output\ScreenOutput;
use App\Output\FileOutput;
use App\Hashtag;

class GetTopData extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'get:top
							{hashtag}
							{--d|depth=100}
							{--f|file=}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Get top hashtags from top posts with this hashtag';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //
		$hashtag = $this->argument('hashtag');
		$this->info('Getting top tags from '. $hashtag);
		$ig = new Instagram();
		$medias = $ig->getCurrentTopMediasByTagName($hashtag);
		$alltags = array();
		foreach ($medias as $media){
		  $tags = HashParser::getTags($media->getCaption());
		  foreach ($tags as $tag){
			if (array_key_exists($tag, $alltags)){
				$alltags[$tag]->addPost();
				$alltags[$tag]->addLikes($media->getLikesCount());
			}else{
			  $alltags[$tag] = new Hashtag($tag, 1, $media->getLikesCount());
			}
		  }
		}
		uasort($alltags, [HashSorter::class, 'sortByCount']);

		if ($filename = $this->option('file')){
		  if ($filename == "auto"){
			$filename = "./output/" . $this->argument('hashtag') . ".txt";
		  }
		  $output = new FileOutput($filename);
		} else {
		  $output = new ScreenOutput();
		}

		$output->outputHashtags($alltags);
		$this->info('Done');


    }

    /**
     * Define the command's schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }

}
