<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace Orm\Interfaces;

interface Adapter
{
    public function connect();

    public function disconnect();
}
