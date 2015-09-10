<?php 
    function parseRawList($rawList) 
    { 
      //if you want the dots (. & ..) set this variable to 0 
      $start = 2; 
      //specify the order of the contents here 
      //currently set to first directories "d" 
      //second links "l" 
      //and last the files "-" 
      //change it to your convenience but don't touch the value names! 
      $orderList = array("d", "l", "-"); 
      //the name and the order of the columns 
      //change it to your convenience 
      //but don't increase/reduce the number of columns 
      $typeCol = "type"; 
      $cols = array("permissions", "number", "owner", "group", "size", "month", "day", "time", "name"); 
        
        foreach($rawList as $key=>$value) 
        { 
            $parser = null; 
            if($key >= $start) $parser = explode(" ", preg_replace('!\s+!', ' ', $value)); 
            if(isset($parser)) 
            { 
                foreach($parser as $key=>$item) 
                { 
                    $parser[$cols[$key]] = $item; 
                    unset($parser[$key]); 
                } 
                $parsedList[] = $parser; 
            } 
        } 
        foreach($orderList as $order) 
        { 
            foreach($parsedList as $key=>$parsedItem) { 
                $type = substr(current($parsedItem), 0, 1); 
                if($type == $order) { 
                    $parsedItem[$typeCol] = $type; 
                    unset($parsedList[$key]); 
                    $parsedList[] = $parsedItem; 
                } 
            } 
        } 
        return array_values($parsedList); 
    } 
?> 
