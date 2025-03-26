University Name is : Dholakpur University & site name will be 'dhpexam'(http://localhost/Dholakpur-University/)
create a student admit card download system, where student login using their registration number,selecting the semester & click details then the student name will be display & dob(as a pasword) click login!
after login redirect them to the student dashboard page,in the dashboard page display the following details:
1. Student Name
2. Student Registration Number
3. Student Roll Number
4. student type(regular/backlog)
5. Student Course
6. student current semester
7. student department
8. student Program(UG/PG)
9. student mobile number
10. student email id
11. student photo
12. student fathers name
Next Paper Details:
Here display the particular student papers name with paper code serial wise!
 student only view their all details & click proceed to pay fees & download admit card button!
 when click on it then redirect to the razorpay payment gateway page! and after paying the center fees(300rs)succesfully then reidrect to the 'print_receipt' page! here download the admit card! where in the 1st page display the student fees receipt details & in the 2nd page display the admit card details!(Means student name,registration no,roll no,course,semester,student type, then next paper details with exam time table)! & in the right side display the student photo! & must university Logo & name with addres. and at the bottom display the 'controller of examination' name & signature!' use fpdf form pdf library!

Also note one thing if student succesfully pay their semester fees then auto redirect to the 'print_receipt' page!, so student can download their admit card!

USE CORE PHP,CSS3,Bootstrap5(for buttons),Tailwind css(CDN),js library,razorpay payment, nprogress js,google font api,select2,momentsjs,jqueryUI,TCPDF for generating proper pdf.
and add a option that pending attendence,means bydefault students attendence will be pending! after approved they can login
Razorpay credentials:
rzp_test_ykJT9pz3eI8bEH
5bTagqXDtzR4WW23MYyD6Xy2
Use composer for install the fpdf library & razorpay payment gateway!
Make the site clean & responsive!

Now listen create a students table,where include all fields including students all details & 8paper details(means paper name,code,schedule) with the particular papaer exam date & timetable!
a students max have 7 papers! 
create a payments table for storing the payment details.
and for managing the backlog students,create a seperate table for them & for backlog assign the backlog papers & that paper time table!
.............
Also one thing, You know that there are 2 way of examination conducting,ODD & EVEN Semester. and suppose if any students got back 1st sem then they need to clear it on 3rd sem.like this way!