<?php

namespace App\Service;

class Filter
{
    public function getFilters($fields, $filters = []) {

        $data = [];
        foreach($filters as $key) {
            if(isset($fields[$key]) && !empty(trim($fields[$key]))) {
                $data[$key] = $fields[$key];
            }
        }

        if(count($data) > 0) {
            return $data;
        }

        return null;
    }

    public function getSort($fields, $allowFields = []) {

        $data = [];
        foreach($allowFields as $key) {
            if(isset($fields[$key]) && in_array(strtolower($fields[$key]), ['asc', 'desc'])) {
                $data[$key] = strtolower($fields[$key]);
            }
        }

        if(count($data) > 0) {
            return $data;
        }

        return null;
    }
}