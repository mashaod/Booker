<template>
<div class="home-page">
<div v-if="!auth" class="login-page">
  <form-login v-on:authLogin="checkUser"></form-login>  
</div>
<div v-if="auth" class="booker">
    <div>
      <ul class="nav nav-tabs"  @click="changeView('viewCalendar')">
        <li class="head-logo">BOARDROOM BOOKER</li>
        <li v-for="(room, index) in roomsItems" @click="changeRoom(index)" role="presentation" :class="room.class"><a href="#">{{room.name}}</a></li>
        <li class="right-header-block">Hello, {{user.login}} <span class="glyphicon glyphicon-user"></span>
          <button type="button" class="btn btn-info btn-sm header-btn" @click="logout">
            <span class="glyphicon glyphicon-log-out"></span> Log out
          </button>
        </li>
      </ul>
    </div>

    <div v-if="viewCalendar">
      <div class="clearfix">
        <div class="calendar ">
            <div class="date-box">
              <div class="data-list glyphicon glyphicon-chevron-left" v-on:click="minusMonth()"></div>
              <div class="data-select">{{year}} {{monthsItems[month]}}</div>
              <div class="data-list glyphicon glyphicon-chevron-right" v-on:click="plusMonth()"></div>
            </div>
            <table>
                <tr> 
                  <th v-for="(day, index) in daysItems" class="weeks-name-block">
                    {{day}}
                  </th>
                <tr>
                <tr v-for="row in items">
                  <th v-for="day in row" class="cell">
                    <div class="num-day-box">{{day.date}}</div>
                    <div class="events-day-box">
                      <ul class="list-day-events">
                        <li v-for="event in day.events">
                            <a href="#" v-on:click="openWindow(event.id)">{{event.time}}</a>
                        </li>
                      </ul>
                    </div>                      
                  </th>
                </tr>
            </table>         
        </div>
        <div class="right-column">
          <button type="button" class="btn btn-success btn-sm btn-right-column" @click="changeView('viewFormAddEvent')">
            Book it!
          </button>
            <router-link :to="'/employee/'">
              <button v-if="authAdmin" type="button" class="btn btn-default btn-sm btn-right-column">
                Employee List
              </button>
            </router-link>
          <div class="format-time" v-on:click="changeDayArr(viewFormAddEvent)">{{timeFormat.title}} Format</div>
        </div>
      </div>
    </div>
    
    <div v-if="viewFormAddEvent && auth">
        <form-add-event 
        :auth="auth" 
        :authAdmin="authAdmin"
        :user = "user" 
        :timeFormat="timeFormat.value" 
        :activeRoom="activeRoom" 
        v-on:createEvent="createCalendar">
        </form-add-event>
    </div>
</div>
</div>
</template>
<script>
  import axios from 'axios'
  import formAddEvent from './FormAddEvent'
  import formLogin from './FormLogin'

  export default {
    data: () => ({
      id: 8,
      user: {id: '-'},
      auth: false,
      authAdmin: false,
      viewCalendar: true,
      viewFormAddEvent: false,
      viewEmloyeeList: false,
      items:[],
      events: [],
      activeRoom: {id:'1',name:'Boardroom 1'},
      roomsItems: [],
      year: '',
      month: '',
      timeFormat: {title: 'Europe', value: 'europe'},
      monthsItems: ['January','February','March','April','May','June','July','August','September','October','November','December'],
      daysItems: ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'],
    }),
    config: {headers: {'Content-Type': 'application/x-www-form-urlencoded'}},
    created () {
      this.year = new Date().getFullYear()
      this.month = new Date().getMonth()
      this.checkUser()
    },
    watch: {
        timeFormat: function() {
        this.createCalendar()
      }
    },
    methods:{

      //  Check user by hash
      checkUser(){
        var self = this
        var id_user = JSON.parse(localStorage.getItem('id'))
        var hash = JSON.parse(localStorage.getItem('hash'))

        if (hash && id_user){ 
            var data = id_user + '/' + hash
            //axios.get('http://192.168.0.15/~user1/Booker/server/api/auth/' + data)
            axios.get('http://rest/user1/Booker/server/api/auth/'+ data)
            .then(function(response){

              //axios.get('http://192.168.0.15/~user1/Booker/server/api/users/' + id_user)
              axios.get('http://rest/user1/Booker/server/api/users/'+ id_user)
              .then(function(response){
                  self.authAdmin = response.data.role == '1'?true:false;
                  self.auth = true
                  self.user = response.data
                  self.createCalendar()
              })
              .catch(function (error){
                console.log(error)
                self.auth = false
              })         
            })
           .catch(function (error) {
               console.log(error)
               self.auth = false
            })
        }else{
            self.auth = false    
        }  
      },

      //  Get all events
      getEvents(callback){
          var self = this

          self.createRooms(function(events){
              //axios.get('http://192.168.0.15/~user1/Booker/server/api/events/')
              axios.get('http://rest/user1/Booker/server/api/events/')
              .then(function (response) {

                  response.data.forEach(function(event){
                      var obj = {
                                    id: event.id,
                                    utcStart: event.start,
                                    utcEnd: event.end,
                                    roomId: event.room_id,
                                    roomName: event.room_name
                                }
                      events[event.room_id].push(obj)
                  })
                  callback(events[self.activeRoom.id])
              }).catch(function (error) {
                  console.log(error)
              })
        })
      },

      //  Delete local hash data
      logout(){
          localStorage.removeItem('hash')
          localStorage.removeItem('id')
          
          this.checkUser()   
      },

      //  Create calendar with events 
      createCalendar() {

        var self = this
        self.getEvents(function(events){
          
        var year = self.year
        var month = self.month

        var d = new Date(year, month);
        self.items= []
        self.items[0] = []

        for (var i = 0; i < self.getDay(d); i++) {
            self.items[0].push({}) // Тumber of days to skip
        }

          // Сalendar cells with dates
          var row = 0
          while (d.getMonth() == month) {

              var dayEvents = [{id: '', time: ''}]

              var timeDay = d.getDate() + " " + self.monthsItems[month] + " " + year;
              var utcStartDay = parseInt(Date.parse(timeDay).toString().substring(0, 10));
              var utcEndDay = utcStartDay + 86399;

              events.forEach(function(event){
                if (event.utcStart > utcStartDay && event.utcStart < utcEndDay)
                {
                  var dateStart = new Date(event.utcStart * 1000);
                  var dateEnd =  new Date(event.utcEnd * 1000);

                  event.time = dateStart.getHours() + ':' + ('0' + dateStart.getMinutes()).slice(-2);
                  event.time += '-' + dateEnd.getHours() + ':' + ('0' + dateEnd.getMinutes()).slice(-2);
                  
                  // Transform time in USA format
                  if (self.timeFormat.value == "usa"){
                    var startHours = dateStart.getHours()
                    var startMinutes = ('0' + dateStart.getMinutes()).slice(-2)
                    var endHours = dateEnd.getHours()
                    var endMinutes = ('0' + dateEnd.getMinutes()).slice(-2)

                    event.time = (+startHours > 12)? (+startHours - 12) + ':' + startMinutes + ' - ' : startHours + ':' + startMinutes + ' - ';
                    event.time += (+endHours > 12)? (+endHours - 12) + ':' + endMinutes + ' PM' : endHours + ':' + endMinutes + ' AM';
                  }

                  dayEvents.push(event)
                }
              })

              self.items[row].push({date: d.getDate(), events: dayEvents})

            if (self.getDay(d) % 7 == 6) { // Sunday, last day - line feed
              row ++
              self.items[row] = []
            }
        
            d.setDate(d.getDate() + 1);
          }
        })   
    },

    //  Get rooms data and create array rooms for events
    createRooms(callback){
        var self = this        

        //axios.get('http://192.168.0.15/~user1/Booker/server/api/rooms/')
        axios.get('http://rest/user1/Booker/server/api/rooms/')
        .then(function (response) {
          var events = {}
          self.roomsItems = []
          response.data.forEach(function(room){
              room.class = room.id == self.activeRoom.id? room.class = "active" : room.class = "" // active room class
              self.roomsItems.push(room)
              var room = room.id.slice()
              events[room] = []
          })
          callback(events)
        }).catch(function (error) {  
            callback()
            console.log(error)
      })  
    },

    //  Get the day of the week, from 0 (Mon) to 6 (Sun)
    getDay(date) {
        if (this.timeFormat.value == "usa"){
          var day = date.getDay();
          return day;
        }
        else{
          var day = date.getDay();
          if (day == 0) day = 7;
          return day - 1;
        }
    },

    //  Re-create the calendar for the next month 
    plusMonth(){
        if (this.month == 11){
          this.year += 1
          this.month = 0
        }
        else{
          this.month += 1
        }
        this.createCalendar()
    },

    //  Re-create the calendar for the previous month    
    minusMonth(){
        if (this.month == 0){
          this.year -= 1
          this.month = 11
        }
        else{
          this.month -= 1
        }
        this.createCalendar()
    },

    //  Change format week by choose time format
    changeDayArr(){
      if (this.timeFormat.value == "europe"){
        this.timeFormat.value = "usa"
        this.timeFormat.title = "USA"
        this.daysItems = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday']
      }
      else{
        this.timeFormat.value = "europe"
        this.timeFormat.title = "Europe"
        this.daysItems = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday']
      }
      this.createCalendar()
   },

   // Open new window for change event
   openWindow(idEvent){
     window.open('#/event/search\?id='+idEvent,'','Width=560,Height=430');
   },

   // Change view home page
   changeView(activeView){
      this.viewCalendar = false
      this.viewFormAddEvent = false
      this.viewEmloyeeList = false
      this[activeView] = true
    },

   // Change active room
   changeRoom(index){
     this.activeRoom.id = this.roomsItems[index].id
     this.activeRoom.name = this.roomsItems[index].name
     this.roomsItems[0].class = ''
     this.createCalendar()
    }
  },
  components: {
     'form-add-event' : formAddEvent,
     'form-login': formLogin
    } 
}

</script>
<style>
* {
    margin: 0;
    padding: 0;
    -moz-box-sizing: border-box;
    -webkit-box-sizing: border-box;
    box-sizing: border-box;
}
.clearfix:after {
	content: ".";
	display: block;
	height: 0;
	clear: both;
	visibility: hidden;
}

.router-link{
  text-decoration: none;
}

a {
    text-decoration: none;
    font-size: 11px;
    margin-top: 5px;
}

li :hover{
    text-decoration: none;
}

.header-logo{
  cursor: pointer;
}

.header-input{
    width: 140px;
    margin: 0 3px;
}

.right-header-block{
    margin-left: 287px;
}

.header-btn{
    width: 90px;
    margin: 0 8px;
    text-decoration: none;
    background-color: #F5F5F5;
}

table {
    border-collapse: collapse;
}

td,th {
    border: 1px solid black;
    padding: 3px;
    text-align: center;
}

th {
    font-weight: bold;
    background-color: #F5F5F5;
}

tr :hover{
    background-color:  #FFFFFF;
}

.weeks-name-block{
    background-color:  #D3D3D3;
}

.num-day-box{
    color:#696960;
    text-align: left;
    height: 10px;
}

.events-day-box{
    height: 80px;
}

.head-logo{
    cursor: pointer;
    margin: 0 100px 0 100px;
    font-size: 20px;
}

.calendar{
    margin: 10px 0 0 200px;
    float: left;
}

.cell{
    height: 100px;
    width: 115px;
    background-color: #F5F5F5;
    border: 1px solid #C0C0C0;
}

.cell li{
    list-style-type: none;
}

.date-box {
    width: 810px;
    margin: 5px;
    text-align: left;
    padding-left: 265px;
}

.data-list{
  cursor: pointer;
  display: inline-block;
  font-size: 20px;
  margin: 5px;
}

.data-select{
    display: inline-block;
    text-align: center;
    font-size: 20px;
    margin: 5px;
    width: 150px;
}

.right-column{
    padding-top: 220px;
    float: left;
    width: 230px;
}

.btn-right-column{
    cursor: pointer;
    width: 200px;
    height: 30px;
    background-color: #696969;
    padding-top: 3px;
    font-size: 15px;
    margin: 5px;
    box-shadow: 0px 1px 1px 1px rgba(0, 0, 0, .2);
}

.footer-options{
    display:block;
    padding: 5px 0 50px 0;
}

.format-time{
    padding-top: 10px;
    cursor: pointer;
}
</style>