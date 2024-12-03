<?php


//Give a Bugguide image ID, returns the taxon it is posted at
//Also accepts taxon ID as an input
function get_bugguide_taxon_info($imageId) {
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://bugguide.net/node/view/' . $imageId,
        CURLOPT_RETURNTRANSFER => true
    ));
    $response = curl_exec($curl);
    curl_close($curl);

    $pattern = "/<div class=\"bgpage-roots\">([\w\W]*?)<\/div>/";
    preg_match($pattern, $response, $matches);

    //$matches contains all the HTML links. Split on the arrows to get array of <A> tags
    $arr = explode(" &raquo; ", $matches[1]);

    //Remove first 2 entries - Home and Guide
    $output = array_slice($arr, 2);

    //Now get [1] node ID [2] taxonomic rank [3] Node name

    /*
    If no common name:
        Array
        (
            [0] => <a href="https://bugguide.net/node/view/94265" title="Species">Alobates&nbsp;barbatus</a>
            [1] => 94265
            [2] => Species
            [3] => Alobates&nbsp;barbatus
        )
    If common name
        Array
        (
            [0] => <a href="https://bugguide.net/node/view/7292" title="Species">False&nbsp;Mealworm&nbsp;Beetle&nbsp;(Alobates&nbsp;pensylvanicus)</a>
            [1] => 7292
            [2] => Species
            [3] => False&nbsp;Mealworm&nbsp;Beetle&nbsp;(Alobates&nbsp;pensylvanicus)
        )
    */

    preg_match('/<a href="https:\/\/bugguide\.net\/node\/view\/(.*?)" title="(.*?)">(.*?)<\/a>/s', $output[count($output) - 1], $node_info);
    for ($i = 0; $i < count($node_info); $i++) {
        $node_info[$i] = str_replace('&nbsp;', ' ', $node_info[$i]);
    }

    $result = array(
        id => $node_info[1],
        taxon_rank => $node_info[2]
    );

    if (strpos($node_info[3], '(') !== false && strpos($node_info[3], ')') !== false) {
        preg_match('/(.*?) \((.*?)\)/s', $node_info[3], $common_name);
        $result['name'] = $common_name[2];
        $result['common_name'] = $common_name[1];
    } else {
        $result['name'] = $node_info[3];
    }
    return $result;
}

//Give a Bugguide Taxon ID, returns the chain of all taxons above it
function get_bugguide_taxon_chain($imageId) {
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://bugguide.net/node/view/' . $imageId,
        CURLOPT_RETURNTRANSFER => true
    ));
    $response = curl_exec($curl);
    curl_close($curl);

    $pattern = "/<div class=\"bgpage-roots\">([\w\W]*?)<\/div>/";
    preg_match($pattern, $response, $matches);

    //$matches contains all the HTML links. Split on the arrows to get array of <A> tags
    $arr = explode(" &raquo; ", $matches[1]);

    //Remove first 2 entries - Home and Guide
    $output = array_slice($arr, 2);

    //Now get [1] node ID [2] taxonomic rank [3] Node name

    /*
    If no common name:
        Array
        (
            [0] => <a href="https://bugguide.net/node/view/94265" title="Species">Alobates&nbsp;barbatus</a>
            [1] => 94265
            [2] => Species
            [3] => Alobates&nbsp;barbatus
        )
    If common name
        Array
        (
            [0] => <a href="https://bugguide.net/node/view/7292" title="Species">False&nbsp;Mealworm&nbsp;Beetle&nbsp;(Alobates&nbsp;pensylvanicus)</a>
            [1] => 7292
            [2] => Species
            [3] => False&nbsp;Mealworm&nbsp;Beetle&nbsp;(Alobates&nbsp;pensylvanicus)
        )
    */

    $container = array();
    for ($j = 0; $j < count($output); $j++) {

        preg_match('/<a href="https:\/\/bugguide\.net\/node\/view\/(.*?)" title="(.*?)">(.*?)<\/a>/s', $output[$j], $node_info);
        for ($i = 0; $i < count($node_info); $i++) {
            $node_info[$i] = str_replace('&nbsp;', ' ', $node_info[$i]);
        }

        $result = array(
            id => $node_info[1],
            taxon_rank => $node_info[2]
        );

        if (strpos($node_info[3], '(') !== false && strpos($node_info[3], ')') !== false) {
            preg_match('/(.*?) \((.*?)\)/s', $node_info[3], $common_name);
            $result['name'] = $common_name[2];
            $result['common_name'] = $common_name[1];
        } else {
            $result['name'] = $node_info[3];
        }
        $container[] = $result;
    }
    return $container;
}
 
    // print_r(get_bugguide_taxon_chain(7292));

    // echo $response;
?>