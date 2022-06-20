<?php

namespace App\Console\Commands;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use League\HTMLToMarkdown\HtmlConverter;
use voku\helper\HtmlDomParser;
use WpOrg\Requests\Requests;

class Spider extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'spider {url}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';
    public string $cookie='';
    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $url = $this->argument('url');
       $res=new Client([ 'timeout' => 3000, 'connect_timeout' => 3000, 'http_errors' => false, 'verify' => false,
           'headers' => ['Content-Type' => 'application/json', 'X-Requested-With' => 'XMLHttpRequest','cookies'=>true]
       ]);
        $headers=['Cookie'=>$this->cookie,'Referer'=>'https://learnku.com','sec-ch-ua'=>'" Not A;Brand";v="99", "Chromium";v="102", "Google Chrome";v="102"','User-Agent'=>'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/102.0.5005.63 Safari/537.36','X-PJAX'=>true,'X-PJAX-Container'=>'body','X-Requested-With'=>'XMLHttpRequest'];
       $response=$res->get($url,[
           'headers' => $headers
       ]);
       if ($response->getStatusCode()==200){
           $url=[];
           $data=$response->getBody()->getContents();
           $dom = HtmlDomParser::str_get_html($data);
           $element = $dom->find('a.article-link');
           $folder=$dom->findOne('.rm-link-color')->text;
           
           foreach ($element as $item){
                $url[$item->text] = $item->attr['href'];
           }
           foreach ($url as $k=>$u){
               $response1= Requests::get($u,$headers);
               $data1=$response1->body;
               $dom1=HtmlDomParser::str_get_html($data1);
               $file=$dom1->findOne('div.content-body');
               $converter = new HtmlConverter(['preserve_comments' => true,'strip_tags' => true,'remove_nodes' => 'span a']);
               $markdown = $converter->convert($file);
               $disk=Storage::disk('public');
               $folder= preg_replace('#[/*\"|\'\`]#', '', $folder);
               if (!$disk->exists($folder)){
                   $disk->makeDirectory($folder);
               }
               $disk->put($folder.'/'.$k.'.md',$markdown);
           }
       }
       
    }
}
