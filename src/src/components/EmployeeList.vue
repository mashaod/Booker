<template>
<div class="employee-page">
<div v-if="!authAdmin">
    <p>You don't have access</p>
</div>
<div v-if="authAdmin" class="booker">
    <div>
      <router-link :to="'/'">
      <ul class="nav nav-tabs">
        <li class="head-logo">BOARDROOM BOOKER</li>
      </ul>
      </router-link>
    </div>
    <div class="list-box">
    <!-- Employee list -->
        <div :class="messageType">{{messageText}}</div>

        <!-- Form add new employee -->
        <div class="add-employee-btn"><button @click="addView = !addView">New employee</button></div>
        <div v-if="addView" class="add-info-box">
            <div class="add-info-row" :class="classSelectType.user" @click="typeEmployee = 'user'">
                <div class="add-info-block">1. Enter new employee name (required)</div>
                <div class="add-info-block">2. Enter new employee email (required)</div>
            </div>
            <div class="add-info-row" :class="classSelectType.admin" @click="typeEmployee = 'admin'">
                <div class="add-info-block">1. Enter new administrator's name (required)</div>
                <div class="add-info-block">2. Enter new administrator's email (required)</div>
            </div>
            <div class="add-info-row">
                <div class="add-info-block"><input v-model="newLogin" placeholder="Login" type="text"></div>
                <div class="add-info-block"><input v-model="newEmail" placeholder="Email" type="email"></div>
            </div>
            <div class="add-info-row">
                <input v-model="newPassword" id="password-inp" placeholder="password" type="password">
            </div>
            <div class="add-info-btn"><button  @click="addEmployee()">ADD</button></div>
        </div>

        <!-- List active employees in system -->
        <ul class="list-group" v-for="(user, index) in users">
            <li class="list-group-item user-item">
                <div><button @click="deleteUser(users[index])">Delete</button></div>
                <div><button @click="user.show = !user.show">Edit</button></div>
                <div class="login-input"><a :href="'mailto:'+user.email">{{user.login}}</a></div>
            </li>
            <li v-if="user.show" class="list-group-item user-info-box">
            <form @submit.prevent="updateUser(users[index])"> 
                <input type="text" v-model="user.login" :value="user.login">
                <input type="email" v-model="user.email" :value="user.email">
                <button type="submit">Save</button>
            </form>
            </li>
        </ul>
    </div>
</div>
</div>
</template>
<script>
  import axios from 'axios'

  export default {
    data: () => ({
      authAdmin: false,
      addView: false,
      typeEmployee: 'user',
      newLogin: '',
      newEmail: '',
      newPassword: '',
      classSelectType: {user:'select-type',admin:''},
      users: [],
      messageType: '',
      messageText: '',
    }),
    config: {headers: {'Content-Type': 'application/x-www-form-urlencoded'}},
    created () {
        this.checkUser()
    },
    watch:{
        typeEmployee: function(type){
            this.classSelectType = {admin: '', user: ''}
            this.classSelectType[type] = 'select-type'
            this.typeEmployee = type
        }
    },
    methods:{

      // Check user for acting with page   
      checkUser(){
        var self = this
        var id_user = JSON.parse(localStorage.getItem('id'))
        var hash = JSON.parse(localStorage.getItem('hash'))

        if (hash && id_user){ 
            var data = id_user + '/' + hash
            //axios.get('http://192.168.0.15/~user1/Booker/server/api/auth/' + data)
            axios.get('http://rest/user1/Booker/server/api/users/'+ data)
            .then(function(response){

                //axios.get('http://192.168.0.15/~user1/Booker/server/api/users/' + id_user)
                axios.get('http://rest/user1/Booker/server/api/users/'+ id_user)
                .then(function(response){
                    self.authAdmin = response.data.role == '1' ? true : false;
                    self.getAllUsers()
                })
                .catch(function (error){
                    console.log(error)
                    self.authAdmin = false
                })         
            })
           .catch(function (error) {
                console.log(error)
                self.authAdmin = false
            })
        }else{
            self.authAdmin = false    
        }  
      },

      // Get all active users
      getAllUsers(){
        var self = this
        var events = []
        self.users = []

        //axios.get('http://192.168.0.15/~user1/Booker/server/api/users/')
        axios.get('http://rest/user1/Booker/server/api/users/')
        .then(function (response) {

            for(let obj in response.data){
                response.data[obj].show = false
                self.users.push(response.data[obj])
            }

        }).catch(function (error) {
             console.log(error)
        })
      },

      // Edit user data: name, email
      updateUser(user){
        var self = this

            var data = new URLSearchParams();
            data.append('id_user', user.id);
            data.append('login', user.login);
            data.append('email', user.email);
  
            //axios.put('http://192.168.0.15/~user1/Booker/server/api/users/', data, self.config)
            axios.put('http://rest/user1/Booker/server/api/users/', data, self.config)
            .then(function (response) {
                console.log(response.data)
                if(response.data == "Reserved")
                    self.createMessage('error', 'Login was reserved before')
                else
                    self.createMessage('success', 'Success operation update')
        
              })
              .catch(function (error) {
                console.log(error)
                self.createMessage('error', 'Incorrect operation update')
              })
          
            setTimeout(function(){
                self.createMessage('clear')
            }, 6000)
        },

        // Delete user by id and his events in future 
        deleteUser(user){
            var self = this
            var verify = confirm('Are you sure want to delete the user ' + user.login + '?');

            if(verify){
                var data = new URLSearchParams();
                data.append('id_user', user.id);
                data.append('user_status', 'disabled');
                
                //axios.put('http://192.168.0.15/~user1/Booker/server/api/users/', data, self.config)
                axios.put('http://rest/user1/Booker/server/api/users/', data, self.config)
                .then(function (response) {

                    if(response.data == 'Last admin'){
                        self.createMessage('error', 'You can\'t delete last admin')
                    }else{

                        // Delete events by id user
                        axios.delete('http://rest/user1/Booker/server/api/events/-/-/' + user.id)
                        .then(function (response){

                            if(response.data == 'Success')
                                self.createMessage('success', 'Success operation delete user and user\'s events')
                        })
                        .catch(function (error) { 
                        })

                        self.createMessage('success', 'Success operation delete')
                    }

                    self.getAllUsers()       
                })
                .catch(function (error) {
                    console.log(error)
                    self.createMessage('error', 'Incorrect operation delete')
                })
            
                setTimeout(function(){
                    self.createMessage('clear')
                }, 6000)
            }
        },

        // Add new employee by params from form
        addEmployee(){
            if(this.checkEditForm(this.newLogin, this.newEmail) === true)
            {               
                var typeEmployee = this.typeEmployee == 'admin'?1:2

                var data = new FormData();   
                data.append('login', this.newLogin);
                data.append('email', this.newEmail);
                data.append('password', this.newPassword);
                data.append('role', typeEmployee);

                var self = this
                //axios.post('http://192.168.0.15/~user1/Booker/server/api/users/', data, self.config)
                axios.post('http://rest/user1/Booker/server/api/users/', data, self.config)
                .then(function (response) {
                    self.createMessage('success', 'New employee has added')
                    self.getAllUsers()      
                })
                .catch(function (error) {
                    self.createMessage('error', 'Login or Email was reserved before')
                })                
            }
        },
        createEmail(){
            window.location.href = "mailto:mashaod@mail.ru";
        },
        checkEditForm(login, email){
            this.createMessage('clear')
            if(!login || login.length <= 3)
                this.createMessage('error', 'Login must be more then 3')
            else if(!email || !(/^\w+@\w+\.\w{2,4}$/i).test(email))
                this.createMessage('error', 'Incorrect email')
            else if (this.newLogin.length>=5 && this.newPassword.length < 5){
                this.createMessage('error', 'Password must be more then 5')
            }else
                return true
        },
        createMessage(type, text=''){
            this.messageType = type == 'clear'? '': type=='success'?'alert alert-success message-box':'alert alert-danger message-box'
            this.messageText = text
        }
  },
  components: {
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

a, a:hover{
  text-decoration: none;
}

.head-logo{
  margin: 0 100px 0 100px;
  padding-bottom: 14px;
  font-size: 20px;
  color: #696969;
}

.list-box{
    margin-left: 400px;
    margin-top: 20px;
    width: 400px;
}

.user-item{
    text-align: left;
    padding-left: 30px;
    height: 40px;
}

.login-input{
    width: 230px;
    text-align: left;
}

.user-item div button{
    text-decoration: none;
    width: 50px;
    margin: 0 5px;
    border-radius: 3px;
}

.user-info-box input{
    width: 150px;
}

.user-info-box button{
    text-decoration: none;
    width: 50px;
    background-color: #808080;
    color: #FFFFFF;
    margin-right: 5px;
    float: right;
}

.message-box{
    width: 400px;
}

.list-group-item div{
    float: right;
}

.add-employee-btn{
    width: 400px;
    padding: 10px;
}

.add-employee-btn button{
    width: 200px;
    padding: 5px;
}

.add-info-box{
    cursor: pointer;
    width: 400px;
    height: 195px;
    padding: 5px;
    margin: 15px 5px 15px 1px;
    border-radius: 3px;
    box-shadow: 0 0 10px 5px rgba(221, 221, 221, 1);
    font-size: 13px;
    text-align: left;
}

.add-info-block{
    width: 190px;
    padding-left: 10px;
    margin: 5px 0;
    float: left;
}

.add-info-btn{
    width: 400px;
    text-align: center;
}

.add-info-btn button{
    margin: 5px 7px 5px 1px;
    width: 100px;
}

.select-type{
    font-size: 15;
    font-weight: bold;
}

#password-inp{
    width: 362px;
    margin: 3px 10px
}
</style>