import * as globals from "../utils/globals.js";

import { StyleSheet, Text, View, Image } from 'react-native';
import { useState, useEffect, useContext } from 'react';

import { Link } from "expo-router";

import Button from "./Button.js";

import TransactionInfo from "../modals/TransactionInfo.js";
import VerifyAction from "../modals/VerifyAction.js";
import UploadIcon from "../modals/UploadIcon.js";


import Leave from '../assets/images/bx-log-out.svg';
import Upload from '../assets/images/bx-upload.svg';

import { getGroupInfo, leaveGroup } from '../utils/groups.js'

import { ModalContext } from '../modals/ModalContext.js';
import { GlobalContext } from "./GlobalContext.js";


export default function GroupInfo(props) {

    let [groupMembers, setGroupMembers] = useState(null);
    let [transactions, setTransactions] = useState(null);
    let [groupName, setGroupName] = useState(null);
    let [iconPath, setIconPath] = useState(null);

    const setModal = useContext(ModalContext);
    const { currUserID } = useContext(GlobalContext);

    useEffect(() => {
        // React advises to declare the async function directly inside useEffect
        // On load asynchronously request groups and construct the list
        async function getItems() {
            let json = null;

            if (props.id != null) json = await getGroupInfo(props.id);

            if (json !== null) {
                setGroupName(json.group_name);
                setIconPath(json.icon_path);
                setGroupMembers(getGroupMembers(currUserID, json));
                setTransactions(getTransactions(json));
            }            
        }
        getItems();
            
    }, [props.id]);
    if (props.id == null || groupName == null) {
        return (<></>);
    }

    const leave = () => {
        setModal(<VerifyAction label="Are you sure you want to leave this group?" accept={() => leaveGroup(props.id)} />);
    }

    return (
        <View style={{ flexDirection: 'row', flex: 1, height: '100%'}}>
            
            <View style={{ flex: 1, margin: '5em', padding: '2.5em', marginTop: '1em' }} >
                <View style={{ flexDirection: 'row', justifyContent: 'space-between'}}>
                    <View style={globals.styles.listIconAndTextContainer }>
                        <GroupIcon iconPath={iconPath} groupName={groupName} groupID={props.id} />
                        <Text style={[globals.styles.h1, styles.groupName]}>{groupName}</Text>
                    </View>
                    
                    <Button style={[globals.styles.formButton, { width: '15em', margin: 0, marginTop: '.25em' }]} svg={Leave} iconStyle={styles.icon} label='LEAVE GROUP' onClick={leave} />
                </View>
                <View style={styles.listContainer}>
                    <Text style={[globals.styles.h3, { color: globals.COLOR_GRAY, fontWeight: 600, paddingLeft: '1em', paddingBottom: '1.5em' }]}>Members</Text>
                    <View style={styles.listHeader} >

                        <Text style={{ color: globals.COLOR_GRAY, paddingLeft: '2em', fontWeight: '600' }}>USERNAME</Text>
                        <Text style={{ color: globals.COLOR_GRAY, paddingRight: '2em' }}>STANDING</Text>

                    </View>
                    <View style={[globals.styles.list, { marginTop: '.25em', width: '100%', marginBottom: '1em' }]}>
                        {groupMembers}
                    </View>

                </View>

                <View style={styles.listContainer}>
                    <Text style={[globals.styles.h3, { color: globals.COLOR_GRAY, fontWeight: 600, paddingLeft: '1em', paddingBottom: '1.5em' }]}>Transactions</Text>
                    <View style={styles.listHeader} >

                        <Text style={{ color: globals.COLOR_GRAY, paddingLeft: '2em', fontWeight: '600' }}>TRANSACTION</Text>
                        <Text style={{ color: globals.COLOR_GRAY, paddingRight: '2em' }}>YOUR CONTRIBUTION</Text>

                    </View>
                    <View style={[globals.styles.list, { marginTop: '.25em', width: '100%', marginBottom: '1em' }]}>
                        {transactions}
                    </View>

                </View>
            </View> 
        </View>
    );
}

function GroupIcon({ iconPath, groupName, groupID }) {

    const [hover, setHover] = useState(false);
    const setModal = useContext(ModalContext);

    const upload = () => {
        setModal(<UploadIcon groupID={groupID} />);
    }

    return (
        <View onClick={upload}  onMouseEnter={() => setHover(true)} onMouseLeave={() => setHover(false)}>
            <Image
                style={[globals.styles.listIcon, { width: '3em', height: '3em' }]}
                source={iconPath !== null ? decodeURI(iconPath) : globals.getDefaultGroupIcon(groupName)}
            />
            <View style={[{ display: hover ? 'inherit' : 'none'}, styles.uploadContainer]}>
                <Upload style={{ fill: globals.COLOR_WHITE, width: '2em', height: '2em' }} />
            </View>
            
        </View>
        
    );
}

function getGroupMembers(currUserID, json) {

   
    let outputList = [];

    outputList.push(<MemberListItem key={-1} border={false} name="You" id={currUserID} owed={json.debt} />);

    for (let i = 0; i < json['members'].length; i++) {

        outputList.push(<MemberListItem key={i} border={true} name={json['members'][i].username} id={json['members'][i].user_id} owed={json['members'][i].debt} />);
    }

    return outputList;

}

function getTransactions(json) {

    let outputList = [];

    for (let i = 0; i < json['transactions'].length; i++) {

        outputList.push(<TransactionListItem key={i} border={i > 0} name={json['transactions'][i].name} id={json['transactions'][i].transaction_id} owed={json['transactions'][i].user_debt} />);
    }

    return outputList;

}

/**
 *  Assembles DOM elements for a single list entry
 *      @param {number} id           user_id of participant
 *      @param {string} name         username of participant
 *      @param {number} owed         how much the participant paid/owes
 *      @return {React.JSX.Element}  DOM element  
 */
function MemberListItem({ id, name, owed, border }) {

    let text = owed < 0 ? "Is Owed" : "Owes";
    let color = owed < 0 ? { color: globals.COLOR_BLUE } : { color: globals.COLOR_ORANGE };

    return (

        <Link href={'/profile/' + id} asChild>
            <View style={border ? styles.listItemSeperator : styles.listItem} >

                <Text style={globals.styles.listText}>{name}</Text>
                <View style={{ width: 'auto', paddingRight: '.5em', marginTop: '-.5em', marginBottom: '-.5em', minWidth: '5em', alignItems: 'center' }}>
                    <Text style={[globals.styles.listText, { fontSize: '.66em' }, color]}>{text}</Text>
                    <Text style={[globals.styles.listText, color]}>${Math.abs(owed / 100).toFixed(2)}</Text>
                </View>

            </View>
        </Link>

    );
}

/**
 *  Assembles DOM elements for a single list entry
 *      @param {number} id           user_id of participant
 *      @param {string} name         username of participant
 *      @param {number} owed         how much the participant paid/owes
 *      @return {React.JSX.Element}  DOM element  
 */
function TransactionListItem({ id, name, owed, border }) {

    const setModal = useContext(ModalContext);

    let text = owed >= 0 ? "Borrowed" : "Paid";
    let color = owed >= 0 ? { color: globals.COLOR_ORANGE } : { color: globals.COLOR_BLUE };

    const viewTransaction = () => {
        setModal(<TransactionInfo id={id} />);
    }

    return (

        <View style={border ? styles.listItemSeperator : styles.listItem} onClick={viewTransaction} >

            <Text style={globals.styles.listText}>{name}</Text>
            <View style={{ width: 'auto', paddingRight: '.5em', marginTop: '-.5em', marginBottom: '-.5em', minWidth: '5em', alignItems: 'center' }}>
                <Text style={[globals.styles.listText, { fontSize: '.66em' }, color]}>{text}</Text>
                <Text style={[globals.styles.listText, color]}>${Math.abs(owed / 100).toFixed(2)}</Text>
            </View>

        </View>
        

    );
}



const styles = StyleSheet.create({
    groupName: {
        color: globals.COLOR_GRAY,
        borderRadius: 2,
        padding: 0,
        paddingBottom: '.25em',
        paddingLeft: '5%',
        fontWeight: 500,
        flexShrink: 0
    },
    listItem: {
        justifyContent: 'space-between',
        alignItems: 'left',
        flexDirection: 'row',
        marginTop: '.5em',
        paddingBottom: '.5em',
        paddingLeft: '1em'

    },
    listItemSeperator: {
        justifyContent: 'space-between',
        alignItems: 'left',
        flexDirection: 'row',
        borderStyle: 'none',
        borderTopStyle: 'solid',
        borderWidth: '1px',
        borderColor: '#eee',
        paddingTop: '.5em',
        paddingBottom: '.5em',
        paddingLeft: '1em'

    },
    listContainer: {
        marginTop: '2em',
        boxShadow: '0px 0px 5px 5px #eee',
        borderRadius: '1em',
        backgroundColor: globals.COLOR_WHITE
    },
    listHeader: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        borderStyle: 'none',
        borderBottomStyle: 'solid',
        borderWidth: '1px',
        borderColor: '#eee',
        paddingBottom: '.5em'
    },
    icon: {
        fill: globals.COLOR_WHITE,
        width: '1.25em'
    },
    uploadContainer: {
        cursor: 'pointer',
        position: 'absolute',
        width: '3em',
        height: '3em',
        borderRadius: '50%',
        justifyContent: 'center',
        alignItems: 'center',
        backgroundColor: globals.COLOR_MODAL
    }

});