QPM全名是 Quick(or Q's) Process Management Framework for PHP.
PHP 是强大的web开发语言，以至于大家常常忘记PHP 可以用来开发健壮的命令行（CLI）程序以至于daemon程序。
而编写daemon程序免不了与各种进程管理打交道。QPM正式为简化进程管理而开发的类库。

QPM 目前包括4个主要的子模块：
* Process 基础进程管理,包括fork的面向对象封装；
* Supervisor 进程监控,实现了OneForOne、MultiGroupOneForOne 和 TaskFactoryMode 三种模式；
* Pidfile 支持PID文件管理；
* Log 实现了用于测试的简易文件日志，同时支持接入 Psr 标准的日志实现，例如 Monolog。

examples目录下有若干使用的示例，tests是测试用例所在的目录。

获取QPM的方法之一，需要从Github 克隆或下载代码，将其中的 library目录放入 include path（通过.ini或set_include_path()设置都OK）。

例如，qpm checkout后的目录是 /comos/qpm,那么 /comos/qpm/library 就应该被添加到include path。

    <?php
    set_include_path(get_include_path().PATH_SEPARATOR.'/comos/qpm/library') 。
    ?></code>

如果您不需要使用例子和测试用例，可以只使用 library下文件。

QPM也支持通过Composer安装，但目前QPM尚未加入Packagist，所以您在配置Composer时需要自行指定仓库。配置可参考：

    #composer.json
    {
      "repositories": [
        {
            "type": "git",
            "url": "https://github.com/Comos/qpm.git"
        }
      ],
      "require": {
        "monolog/monolog": "1.0.*",
        "Comos/qpm":"0.2.1"
      }
    }


通过Wiki[https://github.com/Comos/qpm/wiki] 可以获取更多信息。
