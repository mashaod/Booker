<template>
<div class="event-info">
    <div class="header-block">B.B.Details</div>
    <div :class="messageType">{{messageText}}</div>
        <table class="table">

            <!-- Event time -->
            <tr>
                <td class="left-col"><div>When:</div></td>
                <td class="row-time">
                    <div class="time-start-box">
                        <select class="select-input-time" id="hour" v-model="hoursStart" :disabled="!actionOptions">
                            <option v-for="(hour, index) in hoursItems" :hour="hour" :value="hour">
                                {{hour}}
                            </option>
                        </select>
                        <span>:</span>
                        <select class="select-input-time" id="minutes" v-model="minutesStart" :disabled="!actionOptions">
                            <option v-for="minutes in minutesItems" :minutes="minutes" :value="minutes">
                                {{minutes}}
                            </option>
                        </select>
                        <span id="time-separator">-</span>
                    </div>
                    <div class="time-end-box">
                        <select class="select-input-time" id="hour" v-model="hoursEnd" :disabled="!actionOptions">
                            <option v-for="(hour, index) in hoursItems" :hour="hour" :value="hour">
                                {{hour}}
                            </option>
                        </select>
                        <span>:</span>
                        <select class="select-input-time" id="minutes" v-model="minutesEnd" :disabled="!actionOptions">
                            <option v-for="minutes in minutesItems" :minutes="minutes" :value="minutes">
                                {{minutes}}
                            </option>
                        </select>
                    </div>                        
                </td>
            </tr>

            <!-- Event notes -->
            <tr>
                <td class="left-col"><div>Notes:</div></td>
                <td><textarea rows="3" cols="43" name="text" v-model="event.description" :disabled="!actionOptions">{{event.description}}</textarea></td>
            </tr>

            <!-- Event author -->
            <tr>
                <td class="left-col"><div>Who:</div></td>
                <td>
                    <select class="select-input" v-model="event.id_user" :disabled="!authAdmin || !actionOptions">
                        <option v-for="user in users" :user="user" :value="user.id">{{user.login}}</option>
                    </select>

                    <!-- Apply recursively -->
                    <div v-if="event.parent && actionOptions" class="apply-flag-block">
                      <span>Apply to all occurrences?</span>
                      <input type="checkbox" v-model="applyFlag" aria-label="123">  
                    </div>
                </td>
            </tr>
        </table>

        <!-- Submitted -->
        <p>Submitted:  {{event.create}}</p>
        <div v-if="actionOptions" class="btn-box">
            <button @click="checkForm()">Edit</button>
            <button @click="deleteEvent()">Delete</button>
        </div>
</div>
</template>
<script>
  import axios from 'axios'

  export default {
    data: () => ({
      auth: false,
      authAdmin: false,
      urlParams: {},
      event: {},
      user: {login:'', id:''},
      users: [],
      moment: '',
      utcDayEvent:'',
      hoursStart: '',
      minutesStart: '',
      hoursEnd: '',
      minutesEnd: '',
      description: '',
      applyFlag: false,
      hoursItems: [8,9,10,11,12,13,14,15,16,17,18,19,20],
      minutesItems: ['00','10','20','30','40','50'],
      minTimeMinutes: '30',
      messageType: '',
      messageText: '',
      actionOptions: true,
      viewAdmin: true,
    }),
    config: {headers: {'Content-Type': 'application/x-www-form-urlencoded'}},
    created () {
        this.checkUser()
    },
    methods:{
      // Get event data: time, notes, author
      getEventInfo(){
          var self = this
          self.parsGetParams(function(arrUrlParams){
            if (!arrUrlParams.id && arrUrlParams =='')
                return false

            var id = arrUrlParams.id    
            //axios.get('http://192.168.0.15/~user1/Booker/server/api/events/' + id)
            axios.get('http://rest/user1/Booker/server/api/events/' + id)
            .then(function (response) {
                self.createEventInfo(response.data)
            }).catch(function (error) {  
                console.log(error)
            }) 
          })
      },

      // Get users for change author by admin
      getAllUsers(){
        var self = this
        self.users = []
        //axios.get('http://192.168.0.15/~user1/Booker/server/api/users/-/disabled/')
        axios.get('http://rest/user1/Booker/server/api/users/-/all')
        .then(function (response) {

            for(let obj in response.data){
                self.users.push(response.data[obj])
            }
            
        }).catch(function (error) {
             console.log(error)
        })
      },

      // Create event information from data
      createEventInfo(event){
        var dateStart = new Date(event.time_start * 1000)
        var dateEnd =  new Date(event.time_end * 1000)
        var dateCreate = new Date(event.time_create * 1000)

        this.hoursStart =  dateStart.getHours()
        this.minutesStart = ('0' + dateStart.getMinutes()).slice(-2)
        this.hoursEnd = dateEnd.getHours()
        this.minutesEnd = ('0' + dateEnd.getMinutes()).slice(-2)
        this.utcDayEvent = event.time_start - (dateStart.getHours() * 3600) - (dateStart.getMinutes() * 60)
        this.moment = (Date.now()/1000).toFixed()

        event.create = dateCreate.toLocaleString()
        this.event = event

        if(this.moment>event.time_start || (event.id_user != this.user.id && this.authAdmin === false))
            this.actionOptions = false;
      },

      // Change form before edit event
      checkForm(){
        var utcTimeStart = this.utcDayEvent + (this.hoursStart * 3600) + (this.minutesStart * 60)
        var utcTimeEnd = this.utcDayEvent + (this.hoursEnd * 3600) + (this.minutesEnd * 60)

        if (utcTimeStart > this.moment)
        {
            if (utcTimeEnd>utcTimeStart && utcTimeEnd > this.utcDayEvent)
            {
                if ((utcTimeEnd - utcTimeStart) >= (+this.minTimeMinutes * 59))
                {            
                    this.updateEvent(utcTimeStart, utcTimeEnd)
                }
                else     
                    this.createMessage('error', 'Minimum time for event is ' + this.minTimeMinutes + " minutes")
            }
            else     
                this.createMessage('error', 'Incorrect date, maybe the end of events before the beginning')
        }
        else
            this.createMessage('error', 'You try change moment in past')
      },

      // Edit event data: time, notes, author
      updateEvent(startTime, endTime){
        var self = this

        var data = new URLSearchParams();
        data.append('id_event', self.event.id);
        data.append('id_user', self.event.id_user);      
        data.append('time_start', startTime);
        data.append('time_end', endTime);
        data.append('description', self.event.description);
        data.append('apply_flag', self.applyFlag);
        
        //axios.put('http://192.168.0.15/~user1/Booker/server/api/events/', data, self.config)
        axios.put('http://rest/user1/Booker/server/api/events/', data, self.config)
        .then(function (response) {

            if (response.data == "Reserved")
                self.createMessage('error', 'Time was rezerved before')
            else
                self.createMessage('success', 'Success operation update')
          })
          .catch(function (error) {
            console.log(error)
            self.createMessage('error', 'Incorrect operation update')
          })
      },

      // Delete event by id
      deleteEvent(){
        var self = this
        var params =  self.event.id + '/' + self.applyFlag;
  
        //axios.delete('http://192.168.0.15/~user1/Booker/server/api/events/' + params)
        axios.delete('http://rest/user1/Booker/server/api/events/' + params)
        .then(function (response) {
            self.createMessage('success', 'Success operation delete')    
        })
            .catch(function (error) {
            console.log(error)
            self.createMessage('error', 'Incorrect operation delete')
        })
      },

      // Create params from URL data
      parsGetParams(callback){
        var url = location.href
        var urlArr = (url.slice(url.indexOf("?")+1,url.length)).split("&")
        
        var arrUrlParams = {}
        for (var i=0; i<urlArr.length; i++){
            var info = urlArr[i].split("=");
            info[0] != '' && info[1] != ''?arrUrlParams[info[0]]=info[1]:''
        }
        callback(arrUrlParams)
      },
      createMessage(type, text=''){
        this.messageType = type == 'clear'? '': type=='success'?'alert-success message-box':'alert-danger message-box'
         this.messageText = text
      },

      // Check user for acting with page 
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
                  self.getEventInfo()
                  self.getAllUsers()
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
      }
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

a :hover{
  text-decoration: none;
}

.event-info{
    margin-left: 60px;
    width: 450px;
    height: 320px;
    border: 10px solid #A9A9A9;
    background-color: #FFFFFF;
    box-shadow:
    0 1px 4px rgba(0, 0, 0, .3),
    -23px 0 20px -23px rgba(0, 0, 0, .8),
    23px 0 20px -23px rgba(0, 0, 0, .8),
    0 0 40px rgba(0, 0, 0, .1) inset;
}

.header-block{
    height: 25px;
    font-size: 40px;
    margin: 5px 0 30px 0;
}

.select-input{
    text-align: left;
    width: 150px;
}

.table tr{
    text-align: left;
}

.left-col{
    width: 80px;
    text-align: right;
    font-weight: bold;
    padding: 5px;
}

.time-inp input{
    float: left;
    width: 50px;
}

.btn-box{
    width: 450px;
}

.btn-box button{
    text-decoration: none;
    width: 80px;
    margin: 5px;
}

.time-start-box, .time-end-box{
    float: left;
    margin-right: 10px;
}

#time-separator{
    padding-left: 4px;
}

.message-box{
    width: 430px;
    font-size: 12px;
}

.apply-flag-block{
    float: right;
    font-size: 13px;
    padding-right: 20px;
}
</style>