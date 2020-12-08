<?php

if (!function_exists('loop')) {
    /**
     * @param callable $handle
     * @param integer $count
     */
    function loop(callable $handle, int $count)
    {
        if ($count > 0) {
            foreach (range(1, $count) as $i) {
                if ($handle($i) === false) {
                    break;
                }
            }
        }
    }

    if (!function_exists('last')) {
        /**
         * Get the last element from an array.
         *
         * @param array $array
         * @return mixed
         */
        function last(array $array)
        {
            return end($array);
        }
    }
}
