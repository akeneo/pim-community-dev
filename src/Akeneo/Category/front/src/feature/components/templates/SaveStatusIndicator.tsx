import {useSaveStatusContext} from "../../hooks/useSaveStatusContext";

export const SaveStatusIndicator = () => {
    const saveStatus = useSaveStatusContext();
    console.log(saveStatus);

    // const priorityIsHigher = (status, higherStatus) => {
    //   if (status.priority > higherStatus.priority) {
    //     return true;
    //   }
    //   return false;
    // }

    // let currentStatus = saveStatusContext.
    // for (const [key, fieldStatus] of Object.entries(saveStatusContext)) {
    //     // 1. editing
    //     // 2. saving
    //     // 3. error
    //     // 4. saved
    //
    //     if (priorityIsHigher(fieldStatus, currentStatus) ) {
    //       currentStatus = fieldStatus;
    //     }
    //
    //     status = currentStatus.text;
    //     logo = ...;
    // }

    return <div>My Status: {saveStatus}</div>
}
