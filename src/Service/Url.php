<?php

namespace App\Service;

class Url
{
    private $sortKeysNotAllowed = [
        "nameSort" => ['areaSort', 'populationSort'],
        "areaSort" => ['nameSort', 'populationSort'],
        "populationSort" => ['nameSort', 'areaSort'],
    ];

    public function getCurrQuery($request, $addQuery = []) {
        $query = array_merge($request->query->all(), $addQuery);
        $query = array_filter($query);

        foreach($this->sortKeysNotAllowed as $key => $sortKeysNotAllowed) {
            if(isset($addQuery[$key])) {
                foreach($sortKeysNotAllowed as $sortKeyNotAllowed)
                    if(isset($query[$sortKeyNotAllowed]))
                        unset($query[$sortKeyNotAllowed]);
            }
        }

        return http_build_query($query);
    }
}