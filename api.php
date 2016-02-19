<?php
require_once('vendor/autoload.php');
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

$console = new Application();

// Symfony stufs
$console
  ->register('search:audio')
  ->setDefinition(array(new InputArgument('search', InputArgument::OPTIONAL, 'Who shall we greet?', 'world')))
  ->setDescription('Greet someone.')
  ->setHelp('The <info>vk:find</info> search word')
  ->setCode(function (InputInterface $input, OutputInterface $output) {
    // Callback

    $search = $input->getArgument('search');
    $output->writeln('We search : ' . $search);

    $appIDIndex = 0;
    $appsList = include (__DIR__.'/config/apps.php');

    echo "\r\n{$appsList[$appIDIndex]['id']}\r\n";

    $access_token = $appsList[$appIDIndex]['access_token'];
    $secret = $appsList[$appIDIndex]['secret'];

    // $access_token = '64059cedaa84400e0fa660fbcf7d588a78794e4ca8dfd7b861d2f556c49202a38cedff4c26a21b6f551fe';
    // $secret = 'e72c2df9bce21df37c';
    $vkApi = new \Parser\VK($access_token, $secret);

    $file_handle = fopen(__DIR__."/in/in.txt", "r");
    $file = __DIR__."/out/out.txt";
    file_put_contents($file, '');

    while (!feof($file_handle)) {
      sleep(1);
      $line = fgets($file_handle);
      $response = $vkApi->api('audio.get', array('owner_id' => (int)$line, 'need_user' => 0));

      if(isset($response['error']) && $response['error']['error_code'] == 9) {
        sleep(2);

        $appIDIndex++;
        if($appIDIndex > sizeof($appsList)-1) {
          echo "\r\nLimit ended. Maybe we need more apps :)\r\n";
          die(); // sorry
        }
        echo "\r\n{$appsList[$appIDIndex]['id']}\r\n";

        $access_token = $appsList[$appIDIndex]['access_token'];
        $secret = $appsList[$appIDIndex]['secret'];
        $vkApi = new \Parser\VK($access_token, $secret);
        $response = $vkApi->api('audio.get', array('owner_id' => (int)$line, 'need_user' => 0));
      }

      echo "\n";
      if (isset($response['response']['items'])) {
        foreach ($response['response']['items'] as $res) {
          $title = preg_match("/" . $search . "/i", mb_strtolower($res['title']));
          $artist = preg_match("/" . $search . "/i", mb_strtolower($res['artist']));
          if ($artist || $title) {
            $status = 'true';
          } else {
            $status = 'false';
          }
          if ($status == 'true') {
            echo $res['title'];
            echo "\n";
            echo $res['artist'];
            echo "\n";
            file_put_contents($file, $line, FILE_APPEND | LOCK_EX);
          }
        }
      }
    }
    file_put_contents($file, 'end', FILE_APPEND | LOCK_EX);
  });
$console->run();