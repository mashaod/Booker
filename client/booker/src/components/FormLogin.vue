<template>
    <div class="login-component">
            <div class="login-form">
                <div class="register-header">
                    <h3>Welcome to BOARDROOM BOOKER</h3>
                    <h5>For next step you need login in system</h5>
                    <div :class="messageType">{{messageText}}</div>
                </div>
                <form @submit.prevent="checkLogin" class="form-box">
                    <div><input v-model="login" type="text" name="login" placeholder=" Login"></div>
                    <div><input v-model="password" type="password" name="password" placeholder=" Password"></div>
                    <button type="submit" class="btn-info register-btn">
                        Login
                    </button>
                </form>
            </div>
        </div> 
</template>
<script>
  import axios from 'axios'

  export default {
    props: [],
  
    data() {
      return {
          login: '',
          password: '',
          messageType: '',
          messageText: '',
          config: {headers: {'Content-Type': 'application/x-www-form-urlencoded'}},
      }
    },
    methods: {
        checkLogin() {           
            if(this.checkForm(this.login) === true)
            {               
                var data = new URLSearchParams();   
                data.append('login', this.login);
                data.append('password', this.password);

                var self = this
                
                axios.put('http://192.168.0.15/~user1/Booker/server/api/auth/', data, self.config)
                .then(function (response) {
                    localStorage.setItem('hash', JSON.stringify(response.data.hash))
                    localStorage.setItem('id', JSON.stringify(response.data.id_user))
                    self.$emit('authLogin', true);
                    self.login = ''
                    self.password = ''
                })
                .catch(function (error) {
                    self.createMessage('error', 'Login or Email incorrect')
                })                
            }
        },
        checkForm(login){
            this.createMessage('clear')
            if(!login || login.length <= 3)
                this.createMessage('error', 'Login must be more then 3')
            else
                return true
        },
        createMessage(type, text=''){
            this.messageType = type == 'clear' ? '' : type == 'success' ? 'alert alert-success message-box' : 'alert alert-danger message-box'
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

.register-header{
    text-align: center;
}

a {
    text-decoration: none;
    color: #FFFFFF;
}

.login-component{
    text-align: center;
}

.form-box{
    margin-top: 50px;
}

.form-box div{
    margin: 7px;
}

.form-box input {
    width: 235px;
    height: 30px;
}

.form-box button{
    cursor: pointer;
    margin: 5px 0 0 0;
    width: 235px;
    height: 30px;
}
</style>
