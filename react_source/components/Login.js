/*
 *  Functions:
 *      submitForm: Creates a post request to /login.php containing values of username, and password fields.
 *          @param: userRef     - reference to username field
 *          @param: passwordRef - reference to password field
 *          @param: errorRef    - reference to error text field to print error text to
 *          
*/

import * as globals from '../utils/globals.js'

import { StyleSheet, Text, View, Image } from 'react-native';
import { Link, router } from "expo-router";
import { useRef } from 'react';

import Button from './Button.js'

const Logo = require('../assets/images/logo/logo-name-64.png');

export default function Login() {

    const onSubmit = () => { submitForm(userRef, passwordRef, rememberRef, errorMessageRef); }

    const errorMessageRef   = useRef(null);
    const userRef           = useRef(null);
    const passwordRef       = useRef(null);
    const rememberRef       = useRef(null);

    return (

        <View style={styles.login}>


            <Image source={Logo} style={styles.logo} />


            <Text style={[globals.styles.label, globals.styles.h2, { padding: 0 }]}>Welcome</Text>
            <Text style={[globals.styles.text, { paddingTop: '1em'}]}>Please sign-in to your account below</Text>

            <Text ref={errorMessageRef} id='loginForm_errorMessage' style={globals.styles.error}></Text>

            <View style={globals.styles.labelContainer}>
                <Text style={[globals.styles.h5, globals.styles.label]}>EMAIL OR USERNAME</Text>
            </View>

            <input tabIndex={1} ref={userRef} placeholder=" Enter your email or username" style={globals.styles.input} id='loginForm_user' name="Username" />

            <View style={globals.styles.labelContainer}>
                <Text style={[globals.styles.h5, globals.styles.label]}>PASSWORD</Text>
                <Link href="/forgot" style={[globals.styles.h5, styles.forgot]}>Forgot Password?</Link> 
            </View>
            <input tabIndex={2} ref={passwordRef} placeholder=" Password" style={globals.styles.input} id='loginForm_password' type='password' name="Password" />

            <View style={[globals.styles.labelContainer, { justifyContent: 'flex-start', paddingTop: 0, width: '78%' }]}>
                <input tabIndex={3} ref={rememberRef} type="checkbox" style={styles.checkbox} id="loginForm_remember" />
                <Text style={[globals.styles.text, { marginTop: '.6em' }]}> Remember Me?</Text>
            </View>

            <Button style={globals.styles.formButton} label='Login' onClick={onSubmit} />

            <View style={{ flexDirection: 'row', justifyContent: 'flex-start', paddingTop: '2em' }}>
                <Text style={globals.styles.text}>New to our platform? </Text>
                <Link href="/signup" style={[globals.styles.text, { color: globals.COLOR_ORANGE }]}>Create an Account</Link>
            </View>

        </View>
    );
}

async function submitForm(userRef, passwordRef, rememberRef, errorRef) {

    // pul username and password in form data for a POST request
    let payload = new URLSearchParams();
    payload.append('user', userRef.current.value);
    payload.append('password', passwordRef.current.value);
    payload.append('remember', rememberRef.current.value);

    // do the POST request
    try {
        let response = await fetch("/login.php", { method: 'POST', body: payload, credentials: 'same-origin' });

        if (response.ok) {
            // redirect
            router.push("/summary");
        }
        else {
            // failed, display error message returned by server
            let responseJSON = await response.json();
            errorRef.current.innerText = responseJSON['message'];
            errorRef.current.classList.remove('hidden');
        }
    }
    catch (error) {
        console.log("error in in POST request to login (/login.php)");
        console.log(error);
    }
}

const styles = StyleSheet.create({
    login: {
        width: '50vh',
        minHeight: '30em',
        height: '60vh',
        backgroundColor: '#FFF',
        minWidth: '25em',
        boxShadow: '0px 0px 5px 5px #eee',
        borderRadius: 18,
        justifyContent: 'center',
        alignItems: 'center',
    },
    logo: {
        height: '3em',
        width: '9em',
        minWidth: '2em',
        borderRadius: 1,
    },
    forgot: {
        padding: 0,
        color: globals.COLOR_ORANGE
    },
    checkbox: {
        marginTop: '.75em',
        color: globals.COLOR_GRAY,
    }

});