# QPM
Q's process management framework for PHP

QPM is a toolkit to simplify PHP multi-process programming.
There're 4 mainly packages in qpm. They are <b>PROCESS</b>, <b>SUPERVISOR</b>, <B>PIDFILE</B> and <B>LOG</B>.
In _examples you can find the several typical usages of QPM. In the _tests directory, you can find the PHPUnit cases.

Before using the qpm, you have to check out the branch to a diretory named <b>qpm</b> and add the parent of qpm to PHP include path.
For example, you checked out the branch to <b>/comos/lib/qpm</b>. <b>/comos/lib</b> must be in PHP include path.

To explore the wiki [https://github.com/Comos/qpm/wiki] to get more informations. 

Q's PHP进程管理框架
QPM是一个PHP多进程编程的工具。
目前包括4个主要的子模块：Process, Supervisor, Pidfile 和Log.
_examples目录下有若干使用的示例，_tests是测试用例所在的目录。

使用QPM前，需要把整个分支checkout 到一个叫做qpm的目录，并且确保qpm的上一级目录被设置为PHP include path（通过.ini或set_include_path()设置都OK）.

例如，qpm所在目录是 /comos/lib/qpm,那么 /comos/lib就应该被添加到include path.

<code><?php
set_include_path(get_include_path().PATH_SEPARATOR.'/comos/lib') 。
?></code>

通过Wiki[https://github.com/Comos/qpm/wiki] 可以获取更多信息。

