<?php
  if ($mycourses = get_my_courses($user->id, 'visible DESC,sortorder ASC', null, false, 21)) {
            $shown=0;
            $courselisting = '';
            foreach ($mycourses as $mycourse) {
                if ($mycourse->category) {
                    if ($mycourse->id != $course->id){
                        $class = '';
                        if ($mycourse->visible == 0) {
                            // get_my_courses will filter courses $USER cannot see
                            // if we get one with visible 0 it just means it's hidden
                            // ... but not from $USER
                            $class = 'class="dimmed"';
                        }
                        $courselisting .= "<a href=\"{$CFG->wwwroot}/user/view.php?id={$user->id}&amp;course={$mycourse->id}\" $class >"
                            . format_string($mycourse->fullname) . "</a>, ";
                    }
                    else {
                        $courselisting .= format_string($mycourse->fullname) . ", ";
                    }
                }

select id,course,name from cf3_diary;  

drop down list with jump to generated.


<a href=newwindow.html target=_blank>New Window</A>
<a href=newwindow.html target=graph>New Graph</A>
<a href="http://chart.apis.google.com/chart?cht=lxy&chdl=Daily&chs=320x160&chco=ff0020,00ff00,0000ff,000000&chd=t:0,0,0,0,0,0|80,80,80,320,320,320" target=graph> <img src="http://chart.apis.google.com/chart?cht=lxy&chdl=Daily&chs=160x80&chco=ff0020,00ff00,0000ff,000000&chd=t:0,0,0|80,80,80" alt="cough" /> </a><
