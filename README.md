# QPM
Quick process management framework for PHP

QPM is a toolkit to simplify PHP multi-process programming.
There're four mainly packages in qpm. They are <b>PROCESS</b>, <b>SUPERVISOR</b>, <B>PIDFILE</B> and <B>LOG</B>.
In examples you can find the several typical usages of QPM. In the tests directory, you can find the PHPUnit cases.

Before using the qpm, you have to check out the branch and add the <b>library</b> directory to PHP include path.
For example, you checked out the branch to <b>/comos/qpm</b>. <b>/comos/qpm/library</b> must be in PHP include path.
<code><?php
set_include_path(get_include_path().PATH_SEPARATOR.'/comos/qpm/library') 。
?></code>

To explore the wiki [https://github.com/Comos/qpm/wiki] to get more informations. 

Q's PHP进程管理框架
QPM是一个PHP多进程编程的工具。
目前包括4个主要的子模块：Process, Supervisor, Pidfile 和Log.
examples目录下有若干使用的示例，tests是测试用例所在的目录。

使用QPM前，需要checkout分支，将其中的 library目录放入 include path（通过.ini或set_include_path()设置都OK）.

例如，qpm checkout后的目录是 /comos/qpm,那么 /comos/qpm/library 就应该被添加到include path.

如果您不需要使用例子和测试用例，可以只使用 library下文件。

<code><?php
set_include_path(get_include_path().PATH_SEPARATOR.'/comos/qpm/library') 。
?></code>

通过Wiki[https://github.com/Comos/qpm/wiki] 可以获取更多信息。
