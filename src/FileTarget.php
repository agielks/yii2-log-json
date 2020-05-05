<?php
/**
 * @link https://tracer.uns.ac.id/
 * @author Agiel K. Saputra <agielkurniawans@gmail.com>
 * @copyright Copyright (c) 2020 UPT TIK UNS
 */

namespace agielks\yii2\jsonlog;

class FileTarget extends \yii\log\FileTarget
{
    use TraitJsonTarget;
}
