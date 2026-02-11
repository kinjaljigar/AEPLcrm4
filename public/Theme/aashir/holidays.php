<!DOCTYPE html>
<html>
  <head>
     <title>Aashir Engineering | Timesheet</title>
    <?php require("head.php"); ?>
    <script type="text/javascript">
    	function openinvoice(url){
    		window.open(url, "_blank", "toolbar=yes,scrollbars=yes,resizable=yes,top=200,left=200,width=600,height=800");
    	}
    </script>
  </head>
  <body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">
      <?php 
        require("nav.php"); 
        require("sidebar.php");
      ?>
      
		<!--  *************************** -->
		<div class="content-wrapper">
			<section class="content">
	     		<div class="container-fluid">
					<div class="row">
						<div class="col-12">
							<div class="card card-primary">
								<div class="card-header">
									<h3 class="card-title">Holidays</h3>
									
								</div>
								<div class="card-body table-responsive p-0">
                                    <table class="table table-hover table-striped table-bordered"><thead><tr><th>DAY</th><th>DATE</th><th>HOLIDAY</th></tr></thead><tbody><tr class="r0"><td><span class="pc">Tuesday</span><span class="mobile_text">Tue</span></td><td class="dt_nowrap"><span class="pc">Jan 14, 2020</span><span class="mobile_text">Jan 14</span></td><td><a href="/holidays/india/pongal.php">Makar Sankranti</a></td></tr><tr class="r1"><td><span class="pc">Sunday</span><span class="mobile_text">Sun</span></td><td class="dt_nowrap"><span class="pc">Jan 26, 2020</span><span class="mobile_text">Jan 26</span></td><td><a href="/holidays/india/republic-day.php">Republic Day</a></td></tr><tr class="r0"><td><span class="pc">Friday</span><span class="mobile_text">Fri</span></td><td class="dt_nowrap"><span class="pc">Feb 21, 2020</span><span class="mobile_text">Feb 21</span></td><td><a href="/holidays/india/maha-shivratri.php">Maha Shivratri</a></td></tr><tr class="r1"><td><span class="pc">Tuesday</span><span class="mobile_text">Tue</span></td><td class="dt_nowrap"><span class="pc">Mar 10, 2020</span><span class="mobile_text">Mar 10</span></td><td><a href="/holidays/india/holi.php">Holi 2nd Day</a></td></tr><tr class="r0"><td><span class="pc">Wednesday</span><span class="mobile_text">Wed</span></td><td class="dt_nowrap"><span class="pc">Mar 25, 2020</span><span class="mobile_text">Mar 25</span></td><td><a href="/holidays/india/chetichand.php">Chetichand</a></td></tr><tr class="r1"><td><span class="pc">Thursday</span><span class="mobile_text">Thu</span></td><td class="dt_nowrap"><span class="pc">Apr 02, 2020</span><span class="mobile_text">Apr 02</span></td><td><a href="/holidays/india/ram-navami.php">Shree Ram Navmi</a></td></tr><tr class="r0"><td><span class="pc">Monday</span><span class="mobile_text">Mon</span></td><td class="dt_nowrap"><span class="pc">Apr 06, 2020</span><span class="mobile_text">Apr 06</span></td><td><a href="/holidays/india/mahavir-janma-kalyanak.php">Mahavir Janma Kalyanak</a></td></tr><tr class="r1"><td><span class="pc">Friday</span><span class="mobile_text">Fri</span></td><td class="dt_nowrap"><span class="pc">Apr 10, 2020</span><span class="mobile_text">Apr 10</span></td><td><a href="/holidays/us/good-friday.php">Good Friday</a></td></tr><tr class="r0"><td><span class="pc">Tuesday</span><span class="mobile_text">Tue</span></td><td class="dt_nowrap"><span class="pc">Apr 14, 2020</span><span class="mobile_text">Apr 14</span></td><td><a href="/holidays/india/ambedkar-jayanti.php">Dr.Baba Saheb Ambedkar's Birthday</a></td></tr><tr class="r1"><td><span class="pc">Saturday</span><span class="mobile_text">Sat</span></td><td class="dt_nowrap"><span class="pc">Apr 25, 2020</span><span class="mobile_text">Apr 25</span></td><td><a href="/holidays/india/parshuram-jayanti.php">Bhagvan Shree Parshuram Jayanti</a></td></tr><tr class="r0"><td><span class="pc">Monday</span><span class="mobile_text">Mon</span></td><td class="dt_nowrap"><span class="pc">May 25, 2020</span><span class="mobile_text">May 25</span></td><td><a href="/holidays/singapore/hari-raya-puasa.php">Ramjan-Eid (Eid-Ul-Fitra)</a></td></tr><tr class="r1"><td><span class="pc">Saturday</span><span class="mobile_text">Sat</span></td><td class="dt_nowrap"><span class="pc">Aug 01, 2020</span><span class="mobile_text">Aug 01</span></td><td><a href="/holidays/singapore/hari-raya-haji.php">Bakri-Eid-(Eid-Ul-Adha)</a></td></tr><tr class="r0"><td><span class="pc">Monday</span><span class="mobile_text">Mon</span></td><td class="dt_nowrap"><span class="pc">Aug 03, 2020</span><span class="mobile_text">Aug 03</span></td><td><a href="/holidays/india/raksha-bandhan.php">Raksha Bandhan</a></td></tr><tr class="r1"><td><span class="pc">Tuesday</span><span class="mobile_text">Tue</span></td><td class="dt_nowrap"><span class="pc">Aug 11, 2020</span><span class="mobile_text">Aug 11</span></td><td><a href="/holidays/india/janmashtami.php">Janmashtami (Shravan Vad-8)</a></td></tr><tr class="r0"><td><span class="pc">Saturday</span><span class="mobile_text">Sat</span></td><td class="dt_nowrap"><span class="pc">Aug 15, 2020</span><span class="mobile_text">Aug 15</span></td><td><a href="/holidays/india/independence-day.php">Independence Day</a></td></tr><tr class="r1"><td><span class="pc">Sunday</span><span class="mobile_text">Sun</span></td><td class="dt_nowrap"><span class="pc">Aug 16, 2020</span><span class="mobile_text">Aug 16</span></td><td><a href="/holidays/india/parsi-new-year.php">Parsi New Year Day - Pateti (Parsi Shahenshahi)</a></td></tr><tr class="r0"><td><span class="pc">Saturday</span><span class="mobile_text">Sat</span></td><td class="dt_nowrap"><span class="pc">Aug 22, 2020</span><span class="mobile_text">Aug 22</span></td><td><a href="/holidays/india/samvatsari.php">Samvatsari (Chaturthi Paksha) / Ganesh Chaturthi</a></td></tr><tr class="r1"><td><span class="pc">Sunday</span><span class="mobile_text">Sun</span></td><td class="dt_nowrap"><span class="pc">Aug 30, 2020</span><span class="mobile_text">Aug 30</span></td><td><a href="/holidays/india/muharram.php">Muharram (Ashoora)</a></td></tr><tr class="r0"><td><span class="pc">Friday</span><span class="mobile_text">Fri</span></td><td class="dt_nowrap"><span class="pc">Oct 02, 2020</span><span class="mobile_text">Oct 02</span></td><td><a href="/holidays/india/gandhi-jayanti.php">Mahatma Gandhi's Birthday</a></td></tr><tr class="r1"><td><span class="pc">Sunday</span><span class="mobile_text">Sun</span></td><td class="dt_nowrap"><span class="pc">Oct 25, 2020</span><span class="mobile_text">Oct 25</span></td><td><a href="/holidays/india/dussehra.php">Dusshera (Vijaya Dasami)</a></td></tr><tr class="r0"><td><span class="pc">Friday</span><span class="mobile_text">Fri</span></td><td class="dt_nowrap"><span class="pc">Oct 30, 2020</span><span class="mobile_text">Oct 30</span></td><td><a href="/holidays/india/milad-un-nabi.php">Eid-e-Meeladunnabi (Prophet Mohammad's Birthday)</a></td></tr><tr class="r1"><td><span class="pc">Saturday</span><span class="mobile_text">Sat</span></td><td class="dt_nowrap"><span class="pc">Oct 31, 2020</span><span class="mobile_text">Oct 31</span></td><td><a href="/holidays/india/sardar-vallabhbhai-patels-birthday.php">Sardar Vallabhbhai Patel's Birthday</a></td></tr><tr class="r0"><td><span class="pc">Saturday</span><span class="mobile_text">Sat</span></td><td class="dt_nowrap"><span class="pc">Nov 14, 2020</span><span class="mobile_text">Nov 14</span></td><td><a href="/holidays/india/diwali.php">Diwali</a></td></tr><tr class="r1"><td><span class="pc">Monday</span><span class="mobile_text">Mon</span></td><td class="dt_nowrap"><span class="pc">Nov 16, 2020</span><span class="mobile_text">Nov 16</span></td><td><a href="/holidays/india/vikram-samvat-new-year.php">Vikram Samvat New Year Day</a></td></tr><tr class="r0"><td><span class="pc">Monday</span><span class="mobile_text">Mon</span></td><td class="dt_nowrap"><span class="pc">Nov 16, 2020</span><span class="mobile_text">Nov 16</span></td><td><a href="/holidays/india/bhai-bij.php">Bhai Bij</a></td></tr><tr class="r1"><td><span class="pc">Monday</span><span class="mobile_text">Mon</span></td><td class="dt_nowrap"><span class="pc">Nov 30, 2020</span><span class="mobile_text">Nov 30</span></td><td><a href="/holidays/sikh/guru-nanak-birthday.php">Guru Nanak's Birthday</a></td></tr><tr class="r0"><td><span class="pc">Friday</span><span class="mobile_text">Fri</span></td><td class="dt_nowrap"><span class="pc">Dec 25, 2020</span><span class="mobile_text">Dec 25</span></td><td><a href="/holidays/us/christmas.php">Christmas</a></td></tr></tbody></table>
								</div>
							</div>
						</div>
					</div>
				</div>
			</section>
		</div>
      <!--  ************************** -->

      <?php require("footer.php"); ?>
    </div>
    <!-- ./wrapper -->
    <?php require("addjs.php") ?>
  </body>
</html>



<?php 

        function displayTimesheetRow($workhour){
            ?>
                <tr>
                    <td>
                        <select>
                            <option value="0">Select Project</option>
                            <option value="1">Project 1 Title goes here</option>
                            <option value="1">Project 2 Title</option>
                            <option value="1">Project 3 Title</option>
                            <option value="1">Project 4 Title</option>
                        </select>
                    </td>
                    <td>
                        <select>
                            <option value="0">Select Task</option>
                            <option value="1">Task 1 Title goes here</option>
                            <option value="1">Task 2 Title</option>
                            <option value="1">Task 3 Title</option>
                            <option value="1">Task 4 Title</option>
                        </select>
                    </td>
                    <td><?= $workhour; ?> </td>
                                                
                    <td>
                    <textarea name="emailbody" class="form-control" id="emailbody" placeholder="Enter your report" rows="2"></textarea>
                    </td>
                    
                </tr>
            <?php
        }
?>
 