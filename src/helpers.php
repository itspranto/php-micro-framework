<?php

function is_gb()
{
    return stripos($_SERVER['HTTP_USER_AGENT'], 'googlebot') !== FALSE;
}