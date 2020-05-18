<?php
/**
 * @author Agiel K. Saputra <agielkurniawans@gmail.com>
 * @copyright Copyright (c) Agiel K. Saputra
 */

namespace agielks\yii2\jsonlog;

class FileTarget extends \yii\log\FileTarget
{
    use TraitJsonTarget;
}
