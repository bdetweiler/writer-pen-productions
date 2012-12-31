<html>
        <body>
                <?php
                        @include('../conn.inc.php');
                        @include('templates/header.inc');
                        echo "<div align = \"center\">";
                                echo "<table border=\"1\">";
                                function myscandir($dir, $exp, $how='name', $desc=0)
                                {
                                   $r = array();
                                   $dh = @opendir($dir);
                                   if ($dh)
                                   {
                                      while (($fname = readdir($dh)) !== false)
                                      {
                                         if (preg_match($exp, $fname))
                                         {
                                            $stat = stat("$dir/$fname");
                                            $r[$fname] = ($how == 'name')? $fname: $stat[$how];
                                         }
                                      }
                                      closedir($dh);
                                      if ($desc)
                                      {
                                         arsort($r);
                                      }
                                      else
                                      {
                                         asort($r);
                                      }
                                   }
                                   return(array_keys($r));
                                }

                                // Searches the Report directory (using RegEx's) for anything
                                // with a "0" in it (i.e. 16Nov2004).
                                $r = myscandir('./Report/Archive', '[0]', 'name', 1);

                                $array_length = count($r);

                                // Strip .csv from array elements
                                // and rename them to be sorted correctly
                                for($j = 0; $j <= $array_length; ++$j)
                                {
                                   $r[$j] = substr($r[$j], 0, 9);

                                   $day   = substr($r[$j], 0, 2); // returns 2 digit date
                                   $month = substr($r[$j], 2, 3); // returns 3 char month
                                   $year  = substr($r[$j], 5, 4); // returns 4 digit year
                                   $r[$j] = $month . $day . $year; 
                                }

                                // Sort the elements correctly in descending order
                                arsort($r);

                                // Rename the elements again to DD MMM YYYY.
                                for($j = 0; $j <= $array_length; ++$j)
                                {
                                   // Strip .csv from array elements
                                   // and sort array the correct way
                                   $month   = substr($r[$j], 0, 3); // returns 2 digit date
                                   $day = substr($r[$j], 3, 2); // returns 3 char month
                                   $year  = substr($r[$j], 5, 4); // returns 4 digit year
                                   $r[$j] = $day . $month . $year;
                                   $link_name[$j] = $day . ' ' . $month . ' ' . $year; 
                                }

                                $k = 0;
                                for($i = 0; $i <= 13; ++$i)
                                {
                                    echo "<tr>";
                                    for($j = 0; $j <= 4; ++$j)
                                    {
                                       // Display link
                                       echo "<td width=\"15%\" align=\"center\">";
                                       echo "&nbsp;";
                                       if ($r[$k] != null)
                                       {
                                          echo "<a href=\"Report/Archive/" . $r[$k] . ".csv\">";
                                          echo $link_name[$k];
                                          echo "</a>";
                                       }
                                       echo "&nbsp;";
                                       ++$k;
                                       echo "</td>";
                                    }
                                    echo "</tr>";
                                }
                                echo "</div>";
                        ?>
                </table>
        </body>
</html>

