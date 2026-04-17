export const fetchStockLevel = async (id) =>{
    try{
        const response = await fetch(`http://localhost/royal-liquor/admin/api/stock.php?product_id=${id}`)
        
        if(!response.ok){
            throw Error(`Failed to fetch stock level ${response.statusText}`)
        }
        
        const body  =  await response.json();

        return body.data 
    }catch(error){
        return {error:error}
    }
}
