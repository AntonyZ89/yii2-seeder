<?php

if (!function_exists('loop')) {
    /**
     * @param callable $handle
     * @param integer $count
     */
    function loop($handle, $count)
    {
        if ($count > 0) {
            foreach (range(1, $count) as $i) {
                $handle($i);
            }
        }
    }
}
