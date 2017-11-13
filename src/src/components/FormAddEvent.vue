<template>
<div class="form-container">
  <div class="logo-room">{{room.name}}</div>
  <form v-on:submit.prevent="checkForm()">
    <div :class="messageType">{{messageText}}</div>

    <!-- Select user for event -->
    <div class="label-input">1. Booker for:</div>  
    <div v-if="!authAdmin">
        {{user.login}}
    </div>
    <div v-if="authAdmin">
        <select class="select-input"  v-model="user.id" v-on:change="">
            <option v-for="user in users" :user="user" :value="user.id">
                {{user.login}}
            </option>
        </select>
    </div>

    <!-- Event date -->
    <div class="label-input">2. I wood like to book this meeting:</div>
    <div>
        <select class="select-input-small" id="month" v-model="month" v-on:change="createDate()">
            <option v-for="(month, index) in monthsItems" :month="month" :value="index">
                {{month}}
            </option>
        </select>
        <select class="select-input-small" id="day" v-model="day.id">
            <option v-for="day in daysItems" v-if="day.active"  :day="day" :value="day.id">
                {{day.id}}
            </option>
            <option v-else :day="day" :value="day.id" class="alert-danger" disabled>
                {{day.id}}
            </option>
        </select>
        <select class="select-input-small" id="year" v-model="year">
            <option v-for="year in yearsItems" :year="year" :value="year">
                {{year}}
            </option>
        </select>
    </div>

    <!-- Event time -->
    <div class="label-input">3. Specify what the time start and end of the meeting (This will be what people see on the calendar.)</div>
        
        <!-- Time USA -->
        <div v-if="format.usa" class="time-usa">
            <div class="time-start-box">
            <select class="select-input-time" id="hour" v-model="hoursStart">
                <option v-for="(hour, index) in hoursItems.usa.start" :hour="hour" :value="hour">
                    {{hour}}
                </option>
            </select>
            <select class="select-input-time" id="minutes" v-model="minutesStart">
                <option v-for="minutes in minutesItems" :minutes="minutes" :value="minutes">
                    {{minutes}}
                </option>
            </select>
            <select class="select-input-format" id="formatUsaTimeStart" v-model="formatUsaTimeStart">
                <option value="am">AM</option>
                <option value="pm">PM</option>
            </select>       
            </div>
            <div class="time-end-box">
                <select class="select-input-time" id="hour" v-model="hoursEnd">
                    <option v-for="(hour, index) in hoursItems.usa.end" :hour="hour" :value="hour">
                        {{hour}}
                    </option>
                </select>
                <select class="select-input-time" id="minutes" v-model="minutesEnd">
                    <option v-for="minutes in minutesItems" :minutes="minutes" :value="minutes">
                        {{minutes}}
                    </option>
                </select>
                <select class="select-input-format" id="formatUsaTimeEnd" v-model="formatUsaTimeEnd">
                    <option value="am">AM</option>
                    <option value="pm">PM</option>
                </select>       
            </div>
        </div>

        <!-- Time Europe -->
        <div v-else class="time-europe">
            <div class="time-start-box">
                <select class="select-input-time" id="hour" v-model="hoursStart">
                    <option v-for="(hour, index) in hoursItems.europe" :hour="hour" :value="hour">
                        {{hour}}
                    </option>
                </select>
                <select class="select-input-time" id="minutes" v-model="minutesStart">
                    <option v-for="minutes in minutesItems" :minutes="minutes" :value="minutes">
                        {{minutes}}
                    </option>
                </select>
            </div>
            <div class="time-end-box">
                <select class="select-input-time" id="hour" v-model="hoursEnd">
                    <option v-for="(hour, index) in hoursItems.europe" :hour="hour" :value="hour">
                        {{hour}}
                    </option>
                </select>
                <select class="select-input-time" id="minutes" v-model="minutesEnd">
                    <option v-for="minutes in minutesItems" :minutes="minutes" :value="minutes">
                        {{minutes}}
                    </option>
                </select>
            </div>
        </div>

    <!-- Event comment -->
    <div class="label-input">4. Enter the specifics for the meeting. (This will be what people see when they click on an event link.)</div>
    <div><textarea rows="7" cols="60" name="text" v-model="description"></textarea></div>
   
    <!-- Сhoice of repetition -->
    <div class="label-input">5. Is this going to be a reacurring event?</div>
        <div><input name="reacurring-flag" v-model="reacurringFlag" :value="true" type="radio"> Yes</div>
        <div><input name="reacurring-flag" v-model="reacurringFlag" :value="false" type="radio"> No </div>
    
    <!-- Repeat options -->   
    <div class="label-input">6. If it is recurring, specify weekly, be-weekly, or monthly.</div>
        <div><input  :disabled="!reacurringFlag" name="reacurring-type" v-model="reacurringType"  value="weekly" type="radio"> Weekly</div>
        <div><input  :disabled="!reacurringFlag" name="reacurring-type" v-model="reacurringType"  value="be-weekly" type="radio"> Be-weekly </div>
        <div><input  :disabled="!reacurringFlag" name="reacurring-type" v-model="reacurringType"  value="monthly" type="radio"> Monthly </div>
    <div class="label-input">If weekly, or bi-weekly, specify the number of weeks for it ti keep recurring. If to keep recurring. If monthly, specify the number of months. (If you choose 'bi-weekly' and put in an odd number of weeks, the computer will round down)</div>
    <div><input class="duration-input" :disabled="!reacurringFlag" type="text" v-model="duration" :value="duration"> duration (max 4 weeks)</div>
    
    <!-- Sumbit btn -->
    <button class="submit-btn btn-success" type="submit">Submit</button>
</form>
</div>
</template>
<script>
  import axios from 'axios'

  export default {
    props: ['auth', 'authAdmin', 'user', 'timeFormat', 'activeRoom'],
  
    data() {
      return {
          room: this.activeRoom,
          format: {},
          day: {},
          month: '',
          year: '',
          minTimeMinutes: '30',
          hoursStart: '8',
          minutesStart: '00',
          hoursEnd: '8',
          minutesEnd: '30',
          formatUsaTimeStart: 'am',
          formatUsaTimeEnd: 'am',
          utcToday: '',
          utcTimeStart: '',
          utcTimeEnd: '',
          users: [],         
          usersItems: [{title:'Maria', value: 1},{title:'Alex', value: 2},{title:'Colin', value: 3}],
          monthsItems: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sept','Oct','Nov','Dec'],
          daysItems: [],
          yearsItems: [],
          description: '',
          reacurringFlag: false,
          reacurringType: 'weekly',
          duration: '1',
          hoursItems: 
          {
              europe: [8,9,10,11,12,13,14,15,16,17,18,19,20],
              usa: {
                  start:[8,9,10,11,12],
                  end: [8,9,10,11,12]
              }
          }, 
          minutesItems: ['00','10','20','30','40','50'],
          messageType: '',
          messageText: '',
          config: {headers: {'Content-Type': 'application/x-www-form-urlencoded'}},
      }
    },
    created() {
            this.format[this.timeFormat] = true
            this.createToday()
            this.getAllUsers()
    },
    watch: {
        formatUsaTimeStart: function(){
            this.hoursItems.usa.start = (this.formatUsaTimeStart=='am')?[8,9,10,11,12]:[1,2,3,4,5,6,7,8]
            //this.hoursStart = this.hoursItems.usa.start[0]
        },
        formatUsaTimeEnd: function(){
            this.hoursItems.usa.end = (this.formatUsaTimeEnd=='am')?[8,9,10,11,12]:[1,2,3,4,5,6,7,8]
            //this.hoursEnd = this.hoursItems.usa.end[0]
        },
        duration: function(){
             if (this.reacurringType == 'weekly')
                this.duration = this.duration > 4? 4:(this.duration < 1?1:this.duration)
             else if (this.reacurringType == 'be-weekly')
                 this.duration = this.duration > 2? 2:(this.duration < 1?1:this.duration)
             else if (this.reacurringType == 'monthly')
                 this.duration = 1
        },
        reacurringType: function(){
            this.duration = 1
        }
    },
    methods: {
        checkForm(){
            if(!this.user || this.user == '-' || !this.description || this.description == ''){
                this.messageText = 'All fields are requared'
                this.messageType = 'alert alert-danger message-box'
                return false
            }
   
            // Create time by choosen format
            if(this.format.usa){
                 this.hoursStart = this.formatUsaTimeStart == 'am' ? this.hoursStart : +this.hoursStart + 12
                this.hoursEnd = this.formatUsaTimeEnd == 'am' ? this.hoursEnd : +this.hoursEnd + 12    
            }
            this.utcTimeStart = (Date.parse(this.day.id + ' ' + this.monthsItems[this.month] + ' ' + 
                + this.year + ' ' + this.hoursStart + ':' + this.minutesStart)/1000)+1
            this.utcTimeEnd = (Date.parse(this.day.id + ' ' + this.monthsItems[this.month] + ' ' + 
                + this.year + ' ' + this.hoursEnd + ':' + this.minutesEnd)/1000)

            var timeNow = (Date.now()/1000).toFixed()

            if(this.utcTimeEnd > this.utcTimeStart && this.utcTimeStart > timeNow)
            {
                if((this.utcTimeEnd - this.utcTimeStart) >= (+this.minTimeMinutes * 59))
                {

                    if(this.reacurringFlag == true)
                    {
                        var arrTimeStart = [this.utcTimeStart]
                        var arrTimeEnd = [this.utcTimeEnd]

                        switch(this.reacurringType){
                            case "weekly": 
                                if(this.duration <= 4 && this.duration > 0){
                                    for(var i=0; i < this.duration-1; i++){
                                        arrTimeStart.push(arrTimeStart[i]+604800)
                                        arrTimeEnd.push(arrTimeEnd[i]+604800)
                                    }
                                }
                                else
                                    this.createMessage('error', 'Incorrect duration for weekly')   
                            break;
                            case "be-weekly": ;
                                if(this.duration <= 2 && this.duration > 0){
                                    for(var i=0; i < this.duration-1; i++){
                                        arrTimeStart.push(arrTimeStart[i]+1209600)
                                        arrTimeEnd.push(arrTimeEnd[i]+1209600)
                                    }
                                }
                                else
                                     this.createMessage('error', 'Incorrect duration for be-weekly')   
                            break;
                            case "monthly":
                                var month = this.monthsItems[this.month+1]?this.monthsItems[this.month+1]:this.monthsItems[0]
                                arrTimeStart.push((Date.parse(this.day.id + ' ' + month + ' ' + 
                                    + this.year + ' ' + this.hoursStart + ':' + this.minutesStart)/1000)+1)
                                arrTimeEnd.push((Date.parse(this.day.id + ' ' + month + ' ' + 
                                    + this.year + ' ' + this.hoursEnd + ':' + this.minutesEnd)/1000))
                            break;
                        }

                        this.utcTimeStart = arrTimeStart.join(',')
                        this.utcTimeEnd = arrTimeEnd.join(',')
                    }

                    //  Add new Event
                    this.addEvent(this.user.id, this.room.id, this.description, this.utcTimeStart, this.utcTimeEnd, timeNow)
                }
                else     
                    this.createMessage('error', 'Minimum time for event is ' + this.minTimeMinutes + " minutes")
            }
            else     
                this.createMessage('error', 'Incorrect date, maybe the end of events before the beginning, or you have chosen a past date')

        },

        // Add new event by check params from form
        addEvent(idUser, idRoom, description, timeStart, timeEnd, timeNow){

            var data = new FormData();   
            data.append('id_user', idUser);
            data.append('id_room', idRoom);
            data.append('description', description);
            data.append('time_start', timeStart);
            data.append('time_end', timeEnd);
            data.append('time_created', timeNow);

            var self = this

            //axios.post('http://192.168.0.15/~user1/Booker/server/api/events/', data, self.config)
            axios.post('http://rest/user1/Booker/server/api/events/', data, self.config)
            .then(function (response) {
                console.log(response.data)
                if (response.data[0] == self.duration || response.data[0]>self.duration)
                    self.createMessage('success', 'Success all operations')
                else if (response.data[0] == '0')
                    self.createMessage('error', 'Rejected, time was reserved before')
                else if (response.data[0] < self.duration)
                    self.createMessage('error', self.duration-response.data[0] + " event/-s was rejected")

                self.$emit('createEvent', true);      
              })
              .catch(function (error) {
                self.createMessage('error', 'Time was reserved before')
              })
        },

        // Get the current date day, month and year
        createToday(){
            var data = new Date()
            this.day.id = data.getDate()
            this.month = data.getMonth()
            this.year = data.getFullYear()
            this.utcToday = Date.parse(this.day.id + ' ' + this.monthsItems[this.month] + ' ' + this.year)/1000;
            this.createDate()
        },

        // Create days in month and disable weekends
        createDate(){
            this.daysItems = []
            var days_in_month = 32 - new Date(this.year, this.month, 32).getDate()
            if(this.day.id > days_in_month)
                this.day.id = days_in_month

            for(var i=1; i<=days_in_month; i++)
            {
                var day = new Date(this.year, this.month, i).getDay()
                var active = day == 6 || day == 0 ? false : true;
                this.daysItems.push({'id': i, 'active': active})
            }

            this.yearsItems = []
            for(var i=0; i<7; i++)
                this.yearsItems.push(+this.year + i)
        },

        // Get active users for create event 
        getAllUsers(){
            var self = this
            self.users = []

            //axios.get('http://192.168.0.15/~user1/Booker/server/api/users/')
            axios.get('http://rest/user1/Booker/server/api/users/')
            .then(function (response) {
                self.users = response.data
            }).catch(function (error) {
                console.log(error)
            })
        },
        checkTime(){
            this.hoursEnd = this.hoursStart > this.hoursEnd ? this.hoursStart : this.hoursEnd
            this.minutesEnd = this.minutesStart > this.minutesEnd ? this.minutesStart : this.minutesEnd
        },
        createMessage(type, text=''){
            this.messageType = type == 'clear'? '': type=='success'?'alert alert-success message-box':'alert alert-danger message-box'
            this.messageText = text
        }
    }
  }
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>
*{ 
  -moz-box-sizing: border-box;
  -webkit-box-sizing: border-box;
  box-sizing: border-box;
}

h1, h2 {
    font-weight: normal;
}

ul {
    list-style-type: none;
    padding: 0;
}

li {
    display: inline-block;
    margin: 0 10px;
}

a {
    text-decoration: none;
    color: #FFFFFF;
}

.logo-room{
    width: 700px;
    color: #A9A9A9;
    font-size: 20px;
    text-align: center;

    box-shadow: 0 0 15px rgba(0, 0, 0, .1) inset; 
    margin-bottom: 5px;
}

.form-container{
    width: 700px;
    text-align: left;
    margin-left: 300px;
    padding: 10px 0 30px 0;
}

.label-input {
    margin-top: 25px;
}

.select-input{
    width: 216px;
    padding: 3px;
    margin: 5px;
    border: 1px solid grey;
}

.select-input-small{
    padding: 3px 5px;
    margin: 5px;
    border: 1px solid grey;
}

.select-input-small select {
    color: red;
    width: 150px;
    height: 100px;
}

textarea {
    margin: 5px;
    padding: 5px;
    border: 1px solid grey;
}

.time-start-box, .time-end-box {
    margin: 10px 6px;
}

.select-input-time{
    width: 45px;
}

.submit-btn{
    width: 150px;
    height: 30px;
    margin: 25px 0;
}

.duration-input{
    width: 30px;
    margin: 15px 0;
}

.message-box{
    width: 700px;
}

</style>
