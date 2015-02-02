# Package: REEM
# 
# Type: Package
# Title: REEM: unsupervised gene set screening with extraction of expression modules
# Version: 1.0
# Date: 2011-01-11
# Author: Teppei Shimamura and Atsushi Niida
# Program Maintainer: Teppei Shimamura <shima@ims.u-tokyo.ac.jp>
# Description: This package will help you to discover functional expression modules given a priori defined set of genes
#
# SOFTWARE COPYRIGHT NOTICE AGREEMENT
# This software and its documentation are copyright 2010 by Teppei Shimamura and Atsushi Niida.
# All rights are reserved.
#
# REFERENCES
#
# REEM: a desktop application for efficient unsupervised gene set screening
# Niida A*, Shimamura T*, Imoto S, Miyano S. (*equally contributed), submitted.
#
readExprData <- function(x, toNumeric=TRUE){
  junk <- unlist(strsplit(x,"\\."))
  ext <- junk[length(junk)]
  if(ext=="txt"|ext=="tsv"){
    junk <- scan(x,"character",sep="\n")
    labels <- unlist(strsplit(junk[1],"\t"))
    sampleID <- labels[which(labels!=""&tolower(labels)!="description"&tolower(labels)!="name")]
    junk <- junk[-1]
    n <- length(sampleID)
    p <- length(junk)
    probeID <- rep("---",p)
    description <- rep(NA,p)
    expr <- matrix(0,p,n)
    count <- round((1:10/10)*p)
    cat("read data section...\n")
    if(!any(tolower(labels)=="description")){
      for(i in 1:p){
        junk2 <- unlist(strsplit(junk[i],"\t"))
        probeID[i] <- as.character(junk2[1])
        if(toNumeric) expr[i,] <- as.numeric(junk2[-1]) else expr[i,] <- junk2[-1]
        if(any(count==i)){
          tmpId <- which(count==i)
          cat(paste(paste(rep("*",tmpId),collapse=""), tmpId*10, "% complate\n"))
        }
      }
    } else {
      for(i in 1:p){
        junk2 <- unlist(strsplit(junk[i],"\t"))
        probeID[i] <- as.character(junk2[1])
        description[i] <- as.character(junk2[2])
        if(toNumeric) expr[i,] <- as.numeric(junk2[-c(1,2)]) else expr[i,] <- junk2[-c(1,2)]
        if(any(count==i)){
          tmpId <- which(count==i)
          cat(paste(paste(rep("*",tmpId),collapse=""), tmpId*10, "% complate\n"))
        }
      }
    }
  } else if(ext=="gct"){
    junk <- scan(x,"character",sep="\n",skip=1)
    junk2 <- unlist(strsplit(junk[1],"\t"))
    p <- junk2[1]
    n <- junk2[2]
    junk <- junk[-1]
    labels <- unlist(strsplit(junk[1],"\t"))
    sampleID <- labels[which(labels!=""&tolower(labels)!="description"&tolower(labels)!="name")]
    junk <- junk[-1]
    probeID <- rep("---",p)
    description <- rep(NA,p)
    expr <- matrix(0,p,n)
    count <- round(n/10)
    cat("read data section...\n")
    if(any(tolower(labels)!="description")){
      for(i in 1:p){
        junk2 <- unlist(strsplit(junk[i],"\t"))
        probeID[i] <- as.character(junk2[1])
        if(toNumeric) expr[i,] <- as.numeric(junk2[-1]) else expr[i,] <- junk2[-1]
        if(any(count==i)){
          tmpId <- which(count==i)
          cat(paste(paste(rep("*",tmpId),collapse=""), tmpId*10, "% complate\n"))
        }
      }
    } else {
      for(i in 1:p){
        junk2 <- unlist(strsplit(junk[i],"\t"))
        probeID[i] <- as.character(junk2[1])
        description[i] <- as.character(junk2[2])
        if(toNumeric) expr[i,] <- as.numeric(junk2[-c(1,2)]) else expr[i,] <- junk2[-c(1,2)]
        if(any(count==i)){
          tmpId <- which(count==i)
          cat(paste(paste(rep("*",tmpId),collapse=""), tmpId*10, "% complate\n"))
        }
      }
    }
  }
  return(list(sampleID=sampleID,probeID=probeID,expr=expr,description=description))
}

myDist <- function(E,t){
 D <- 1-cor(E,t)
 return(D)
}

eem <- function(E,D,r,nn=10){
  n <- nrow(E)
  p <- ncol(E)
  CC <- apply(D,1,function(x){which(x<r)})
  scores <- rep(4,p)
  if(class(CC)=="list"){
    for(i in 1:p) scores[i] <- mean(D[i,CC[[i]]])
    junk <- sapply(CC,length)
    maxId <- which(junk==max(junk))
    if(length(maxId)>=2) maxId <- maxId[which.min(scores[maxId])]
    C1 <- CC[[maxId]]
  }
  if(class(CC)=="matrix"){
    for(i in 1:p) scores[i] <- mean(D[i,CC[,i]])
    maxId <- which.min(scores)
    C1 <- CC[,maxId]
  }
  if(class(CC)=="integer"){
    maxId <- sample(1:p,1)
    C1 <- maxId
  }
  B1 <- E[,maxId]
  T  <- sort.list(D[maxId,])[1:nn]
  triplets <- combinations(nn,3)
  B <- B1
  C <- C1
  setScore <- scores[maxId]
  for(i in 1:nrow(triplets)){
    t <- apply(E[,T[triplets[i,]]],1,mean)
    tmpD <- myDist(E,t)
    tmpC <- which(tmpD<r)
    tmpScore <- mean(tmpD[tmpC])
    if(((length(C)==length(tmpC))&(tmpScore<setScore))|(length(C)<length(tmpC))) {
      B <- t
      C <- tmpC
      setScore <- tmpScore
    }
  }
  return(list(B1=B1,C1=C1,B=B,C=C,score=setScore))
}

eemNullDist <- function(E,D,M,r,nn=10,b=300){
  p <- ncol(E)
  m <- length(M)
  k <- s <- rep(0,b)
  for(i in 1:b){
    tmpM <- sample(1:p,m,replace=FALSE)
    obj <- eem(E[,tmpM,drop=FALSE],D=D[tmpM,tmpM],r=r,nn=nn)
    k[i] <- length(obj$C)
    s[i] <- sum(myDist(E,obj$B)<r)
  }
  return(list(k=k,s=s))
}

findRadius <- function(E,D,d=0.05,nn=10,maxIter=100,delta=1e-3){
  p <- ncol(E)
  junk <- upper.tri(D,diag=FALSE)
  low <- min(D[junk])
  upp <- max(D[junk])
  mu <- (low+upp)/4
  tau <- (upp-low)/4
  f <- s <- gg <- rep(0,3)
  flag <- c(TRUE,TRUE,TRUE)
  obj <- g <- vector("list",3)
  cat("Find optimal radius with spider algorithm...\n")
  for(iter in 1:maxIter){
    r <- c(mu-tau,mu,mu+tau)
    if(r[1]<0) r[1] <- 0
    if(r[3]>4) r[3] <- 4
    if(flag[1]){
      obj[[1]] <- eem(E,D,r=r[1],nn=nn)
      g[[1]] <- myDist(E,obj[[1]]$B)
      gg[1] <- sum(g[[1]]<r[1])
      f[1] <- abs(gg[1]/p-d)
      s[1] <- obj[[1]]$score
    }
    if(flag[2]){
      obj[[2]] <- eem(E,D,r=mu,nn=nn)
      g[[2]] <- myDist(E,obj[[2]]$B)
      gg[2] <- sum(g[[2]]<r[2])
      f[2] <- abs(gg[2]/p-d)
      s[2] <- obj[[2]]$score
    }
    if(flag[3]){
      obj[[3]] <- eem(E,D,r=mu+tau,nn=nn)
      g[[3]] <- myDist(E,obj[[3]]$B)
      gg[3] <- sum(g[[3]]<r[3])
      f[3] <- abs(gg[3]/p-d)
      s[3] <- obj[[3]]$score
    }
    minId <- which(f==min(f))
    if(length(minId)>=2) minId <- minId[which.min(s[minId])]
    if(iter==1|minId!=2) cat("absolute radius=",r[minId],", relative radius=",gg[minId]/p,"\n",sep="")
    result <- obj[[minId]]
    if(minId==1){
      mu <- mu - tau
      tau <- tau*2
      flag <- c(TRUE,TRUE,TRUE)
    }
    if(minId==2){
      mu <- mu
      tau <- tau/2
      flag <- c(TRUE,FALSE,TRUE)
    }
    if(minId==3){
      mu <- mu + tau
      tau <- tau*2
      flag <- c(TRUE,TRUE,TRUE)
    }
    if(iter>=2){
      if(f[minId]<delta|tau<delta) break
    }
  }
  k <- length(result$C)
  return(list(r=r[minId],d=gg[minId]/p,G=obj[[minId]]$B,GD=g[[minId]]))
}

eemAll <- function(exprFile,setFile,chipFile,
                                collapse=TRUE,uniqueMethod=c("varmax","meanmax","medianmax"),
                                maxSetSize=1000,minSetSize=15,
                                maxGeneSize=8000,filterMethod=c("var","mean","median"),
                                maxIter=100,minPval=1,marginalize=TRUE,
                                d=0.05,nn=10,b=100,delta=1e-3){

  uniqueMethod <- match.arg(uniqueMethod)
  filterMethod <- match.arg(filterMethod)
  messages <- vector("list",5)

  # input files
  if(missing(exprFile)){
    exprFile <- tclvalue(tkgetOpenFile(filetypes = "{{Expression Data Files} {.gct .txt .tsv}}"))
    if(!nchar(exprFile)){
      tkmessageBox(message="No file was selected!")
      stop("Input Error!!")
    } else {
      tkmessageBox(message=paste(exprFile,"was selected as an expression dataset file."))
    }
  }
  if(missing(setFile)){
   setFile <- tclvalue(tkgetOpenFile(filetypes = "{{Gene Set Files} {.gmt}}"))
    if(!nchar(setFile)){
      tkmessageBox(message="No file was selected!")
      stop("Input Error!!")
    } else {
      tkmessageBox(message=paste(setFile,"was selected as a gene set file."))
    }
  }
  if(collapse){
    if(missing(chipFile)){
      chipFile <- tclvalue(tkgetOpenFile(filetypes = "{{Chip Files} {.chip}}"))
      if(!nchar(chipFile)){
        tkmessageBox(message="No file was selected!")
        stop("Input Error!!")
      } else {
        tkmessageBox(message=paste(chipFile,"was selected as a chip annotation file."))
      }
    }
  } else {
    chipFile <- "Not given"
  }

  data <- readExprData(exprFile)
  E <- t(data$expr)
  nativeP <- ncol(E)
  messages[[1]] <- paste(paste("The expression dataset has", nativeP, sep=" "), "native features",sep=" ")
  probeID <- data$probeID
  sampleID <- data$sampleID
  description <- data$description
  junk <- scan(setFile,"character",sep="\n")
  junk <- strsplit(junk,"\t")
  nativeRefSet <- refSet <- sapply(junk,function(x){x[1]})
  refGene <- sapply(junk,function(x){x[-c(1,2)]},simplify=FALSE)
  nativeSetP <- sapply(refGene,length)
  cat(messages[[1]],"\n")

  # collapse features into gene symbols
  if(collapse){
    junk <- strsplit(scan(chipFile,"character",sep="\n",skip=1),"\t")
    refProbeID <- sapply(junk,function(x){x[1]})
    refSymbol <- sapply(junk,function(x){x[2]})
    refDescription <- sapply(junk,function(x){x[3]})
    id <- match(probeID,refProbeID)
    tmpProbeID <- refProbeID[id[!is.na(id)]]
    tmpSymbol <- refSymbol[id[!is.na(id)]]
    tmpDescription <- refDescription[id[!is.na(id)]]
    E <- E[,!is.na(id)]
    scores <- switch(uniqueMethod,
                               varmax=apply(E,2,var,na.rm=TRUE),
                               meanmax=apply(E,2,mean,na.rm=TRUE),
                               medianmax=apply(E,2,median,na.rm=TRUE))
    tmpUniqueSymbol <- sort(unique(tmpSymbol))
    tmpUniqueSymbol <- setdiff(tmpUniqueSymbol,c("---","","-","NA",NA))
    junk <- match(tmpSymbol,tmpUniqueSymbol)
    id <- rep(NA,length(tmpUniqueSymbol))
    for(i in 1:length(tmpUniqueSymbol)){
      if(any(i==junk)){
        tmpId <- which(i==junk)
        id[i] <- tmpId[which.max(scores[tmpId])]
      }
    }
    E <- E[,id[!is.na(id)]]
    probeID <- tmpProbeID[id[!is.na(id)]]
    symbol <- tmpUniqueSymbol[which(!is.na(id))]
    description <- tmpDescription[id[!is.na(id)]]
    messages[[2]] <- paste(paste("After collapsing features into gene symbols, there are", ncol(E), sep=" "), "genes",sep=" ")
    cat(messages[[2]],"\n")
  } else {
    symbol <- probeID
  }

  # gene size filter
  if(maxGeneSize<ncol(E)){
    junk <- switch(filterMethod,
                            mean=apply(E,2,mean),
                            median=apply(E,2,median),
                            var=apply(E,2,var))
    selectId <- sort.list(junk,decreasing=TRUE)[1:maxGeneSize]
    cat("gene size filter:",ncol(E),"-->",maxGeneSize,"\n")
    E <- E[,selectId]
    probeID <- probeID[selectId]
    symbol <- symbol[selectId]
    description <- description[selectId]
    messages[[3]] <- paste(paste("After filtering genes, there are", ncol(E), sep=" "), "genes",sep=" ")
    cat(messages[[3]],"\n")
  }

  setP <- rep(NA,length(refSet))
  for(i in 1:length(refSet)){
    id <- match(refGene[[i]],symbol)
    setP[i] <- length(id[!is.na(id)])
  }

  # gene set size filter
  selectId <- which(setP>minSetSize&setP<maxSetSize)
  if(length(selectId)<length(refSet)){
    junk <- scan(setFile,"character",sep="\n")
    junk <- strsplit(junk,"\t")
    refSet <- sapply(junk[selectId],function(x){x[1]})
    refGene <- sapply(junk[selectId],function(x){x[-c(1,2)]},simplify=FALSE)
    messages[[4]] <- paste("Gene set size filters (min=",minSetSize,", max=",maxSetSize,
                                           ") resulted in filtering out ",length(nativeSetP)-length(refSet),"/",
                                           length(nativeSetP)," gene sets",collapse="",sep="")
    cat(messages[[4]],"\n")
    if(length(refSet)==0) {
      stop("No gene set was selected!!")
    } else if(length(refSet)==1) {
      messages[[5]] <- paste("The remaining 1 gene set was used in the analysis")
      cat(messages[[5]],"\n")
    } else {
      messages[[5]] <- paste("The remaining ",length(refSet)," gene sets were used in the analysis",collapse="",sep="")
      cat(messages[[5]],"\n")
    }
  }

  #normalize expression data
  E <- sweep(E,2,colMeans(E,na.rm=TRUE),"-")
  junk <- apply(E,2,sd,na.rm=TRUE)
  if(any(junk<.Machine$double.eps)) stop("For any genes, the standard deviations were equal to 0")
  E <- sweep(E,2,junk,"/")

  n <- nrow(E)
  p <- ncol(E)
  m <- length(refSet)
  D <- 1 - cor(E)
  obj <- findRadius(E,D,d=d,nn=nn,maxIter=maxIter,delta=delta)
  r <- obj$r
  G <- obj$G
  GD <- obj$GD
  M <- vector("list",m)
  C <- vector("list",m)
  B <- vector("list",m)
  pvalue <- rep(1,m)
  set.p <- rep(0,m)

  # EEM main part with calculation of p-values based on hyper geometric distribution
  cat("Find module genes with extraction of expression modules...\n")
  for(i in 1:m){
    cat(refSet[i],paste("(",i,"/",m,")",sep=""),"\n")
    id <- match(refGene[[i]],symbol)
    selectGene <- refGene[[i]][!is.na(id)]
    M[[i]] <- id[!is.na(id)]
    set.p[i] <- length(M[[i]])
    if(set.p[i]==0) next
    junk <- eem(E[,M[[i]]],D[M[[i]],M[[i]]],r=r,nn=nn)
    C[[i]] <- M[[i]][junk$C]
    B[[i]] <- junk$B
    pvalue[i] <- -log10(phyper(length(junk$C)-1,set.p[i],p-set.p[i],round(p*d,0),lower.tail=FALSE))
    cat("\tNumber of seed genes:",set.p[i],"\n")
    cat("\tNumber of module genes:",length(C[[i]]),"\n")
    cat("\t-log10(p-value):",pvalue[i],"\n")
  }

  # Calculation of more exact p-values based on marginalization of hyper geometric distribution
  corrected.pvalue <- rep(NA,m)
  if(marginalize){
    cat("Calculate more exact p-values based on marginalization of hyper geometric distribution...\n")
    filterId <- which(pvalue>=minPval)
    for(i in filterId){
      cat(refSet[i],paste("(",i,"/",m,")",sep=""),"\n")
      res <- eemNullDist(E,D,M[[i]],r,nn=nn,b=b)
      tmpPvalue <- rep(1,b)
      for(j in 1:b){
        tmpPvalue[j] <- phyper(length(C[[i]])-1,set.p[i],p-set.p[i],res$s[j],lower.tail=FALSE)
      }
      corrected.pvalue[i] <- -log10(sum(tmpPvalue/b))
      cat("\t-log10(p-value):",pvalue[i],"-->",corrected.pvalue[i],"\n")
    }
  }

  dataName <- unlist(strsplit(exprFile,"/"))
  dataName <- dataName[length(dataName)]
  result <- list(dataName=dataName,
                       exprFile=exprFile,
                       setFile=setFile,
                       chipFile=chipFile,
                       refSet=refSet,
                       refGene=refGene,
                       pvalue=pvalue,
                       corrected.pvalue=corrected.pvalue,
                       nativeP=nativeP,
                       p=p,
                       nativeRefSet=nativeRefSet,
                       nativeSetP=nativeSetP,
                       setP=setP,
                       set.p=set.p,
                       n=n,
                       E=E,
                       D=D,
                       r=r,
                       sampleID=sampleID,
                       probeID=probeID,
                       description=description,
                       symbol=symbol,
                       M=M,
                       B=B,
                       C=C,
                       G=G,
                       GD=GD,
                       messages=messages,
                       collapse=collapse,
                       uniqueMethod=uniqueMethod,
                       maxSetSize=maxSetSize,
                       minSetSize=minSetSize,
                       d=d,
                       nn=nn,
                       maxGeneSize=maxGeneSize,
                       filterMethod=filterMethod,
                       maxIter=maxIter,
                       minPval=minPval,
                       marginalize=marginalize,
                       b=b,
                       delta=delta)
  return(result)

}

eemReport <- function (obj,dirname) {

    HTwrap <- function(x, class, className, tag = "TD") {
        if(missing(className)) className <- rep("class",length(x))
        if(missing(class)) paste("<", tag, ">", x, "</", tag, ">", sep = "")
        else paste("<", tag, " ",className,"=\'", class, "\'>", x, "</", tag, ">", sep = "")
    }
    HRwrap <- function(x,href){
        paste("<a href=\'", href, "\'>", x, "</a>", sep = "")
    }
    gsubAnchor <-function (id, urlString) {
      test <-  function(x){
        if(!is.na(x))
          res <- gsub(pattern="UNIQID", replacement=x, urlString)
        else
          res <- x
        return(res)
      }
      paste("<A HREF=", sapply(as.character(id), test), ">", id, "</A>", sep = "")
    }
    myhclust <- function(x) hclust(as.dist(1-cor(t(as.matrix(x)), method = "pearson")), method = "ward")
    heatmapCol <- function (data, col, lim) {
      nrcol <- length(col)
      data.range <- range(data)
      if (diff(data.range) == 0) stop("data has range 0")
      if(missing(lim)) lim <- min(abs(data.range))*0.7
      nrcol <- length(col)
      reps1 <- ceiling(nrcol * (-lim - data.range[1])/(2 * lim))
      reps2 <- ceiling(nrcol * (data.range[2] - lim)/(2 * lim))
      col1 <- c(rep(col[1], reps1), col, rep(col[nrcol], reps2))
      return(col1)
    }

    if(missing(dirname)) dirname <- paste("eem",gsub(" |-|:","",as.character(Sys.time())),sep="_")
    junk <- dir.create(dirname)
    if(!junk) stop("Cannot create directory!!")

    # create parameter information
    restable <- cbind(c("exprFile","setFile","chipFile","collapse","uniqueMethod","maxSetSize","minSetSize","maxGeneSize","filterMethod","maxIter","minPval","marginalize","d","nn","b","delta"),
                                c(obj$exprFile,obj$setFile,obj$chipFile,obj$collapse,obj$uniqueMethod,obj$maxSetSize,obj$minSetSize,obj$maxGeneSize,obj$filterMethod,obj$maxIter,
                                   obj$minPval,obj$marginalize,obj$d,obj$nn,obj$b,obj$delta))
    write.table(restable,paste(dirname,"/parameters.xls",sep=""),col.names=FALSE,row.names=FALSE,sep="\t",quote=FALSE)

    # create heatmap and coherence plots
    sampleID <- obj$sampleID
    for(i in 1:length(obj$refSet)){
      tmpE <- t(obj$E[,obj$M[[i]]])
      tmpD <- obj$D[obj$M[[i]],obj$M[[i]]]
      tmpCol <- heatmapCol(tmpE,col=colorpanel(100,low="blue",mid="white",high="red"))
      tmpSymbol <- obj$symbol[obj$M[[i]]]
      tmpEnrichedSymbol <- obj$symbol[obj$C[[i]]]
      enrichCol <- rep("black",nrow(tmpE))
      enrichId <- match(tmpEnrichedSymbol,tmpSymbol)
      enrichCol[enrichId] <- "red"
      dimnames(tmpE) <- list(tmpSymbol,sampleID)

      junk <- myDist(obj$E[,obj$M[[i]]],obj$B[[i]])
      junk2 <- obj$GD
      junk3 <- ecdf(junk)
      junk4 <- ecdf(junk2)
      maxx <- max(c(junk,junk2))
      minx <- min(c(junk,junk2))
      png(paste(paste(dirname,"/",sep=""),paste(obj$refSet[i],"_chrplot.png",sep=""),sep=""),width=640,height=640)
      par(mfrow=c(1,1))
      plot(junk3,xlim=c(minx,maxx),verticals=TRUE,do.p=FALSE,col="red",main="Empirical Cumulative Distribution Function",xlab="Metric score from center")
      lines(junk4,verticals=TRUE,do.p=FALSE)
      abline(v=obj$r,lty=2)
      axis(1,obj$r,round(obj$r,2))
      axis(3,obj$r,"optimal radius")
      dev.off()

      png(paste(paste(dirname,"/",sep=""),paste(obj$refSet[i],"_heatmap.png",sep=""),sep=""),width=640,height=640)
      par(mfrow=c(1,1))
      heatmap.2(tmpE, hclustfun=myhclust, col = tmpCol,
                          RowSideColors=enrichCol,
                          cexRow = 1/log10(nrow(tmpE)), cexCol = 1/log10(ncol(tmpE)),
                          trace = "none", tracecol = "black")
      dev.off()

    }

    # create css file
    outfile <- file(paste(dirname,"/style.css",sep=""),"w")
    cat(css,file=outfile)
    close(outfile)

    # create detail html and excel files
    for(i in 1:length(obj$refSet)){
      junk <- myDist(obj$E,obj$B[[i]])
      junk2 <- rep("No",length(obj$M[[i]]))
      junk2[match(obj$C[[i]],obj$M[[i]])] <- "Yes"
      restable <- cbind(obj$probeID[obj$M[[i]]],obj$symbol[obj$M[[i]]],obj$description[obj$M[[i]]],
                                   rank(junk,ties.method="min")[obj$M[[i]]],junk[obj$M[[i]]],junk2)
      restable <- cbind(1:nrow(restable),restable[sort.list(as.numeric(restable[,5])),,drop=FALSE])
      colnames(restable) <- c("","PROBE","GENE SYMBOL","DESCRIPTION","RANK IN GENE LIST","RANK METRIC SCORE","CORE COHERENT")
      write.table(restable,paste(paste(dirname,"/",sep=""),paste(obj$refSet[i],".xls",sep=""),sep=""),col.names=TRUE,row.names=FALSE,sep="\t",quote=FALSE)
      tmpEntrezLink <- paste(paste("http://www.ncbi.nlm.nih.gov/entrez/query.fcgi?cmd=search&db=gene&term=",restable[,3],sep=""),"[sym]",sep="")
      tmpGeneCardsLink <- paste("http://www.genecards.org/cgi-bin/carddisp.pl?gene=",restable[,3],sep="")
      junk3 <- HTwrap(restable[,7])
      if(any(restable[,7]=="Yes")){
        tmpId <- which(restable[,7]=="Yes")
        junk3[tmpId] <- HTwrap(restable[tmpId,7],rep("lightgreen",length(tmpId)),"bgcolor")
      }
      mainTable <- cbind(HTwrap(restable[,1]),HTwrap(restable[,2]),HTwrap(restable[,3]),HTwrap(restable[,4]),
                                      HTwrap(paste(HRwrap(rep("Entrez",nrow(restable)),tmpEntrezLink),HRwrap(rep("GeneCards",nrow(restable)),tmpGeneCardsLink),sep=",&nbsp")),
                                      HTwrap(restable[,5]),HTwrap(round(as.numeric(restable[,6]),3)),junk3)
      mainTable <- apply(mainTable,1,paste,collapse="")
      headings <- paste(HTwrap(c("","PROBE","GENE SYMBOL","DESCRIPTION","LINK","RANK IN GENE LIST","RANK METRIC SCORE","CORE COHERENT"),class=rep("mytable",8),tag="TH"),collapse="")
      outfile <- file(paste(paste(dirname,"/",sep=""),paste(obj$refSet[i],".html",sep=""),sep=""),"w")
      cat("<html>", file = outfile)
      cat(HTwrap(paste(HTwrap("Report for EEM", tag = "TITLE"),"<link href=\'style.css\' rel=\'stylesheet\'>",sep=""), tag = "head"), "\n", file = outfile)
      cat("<body>\n",file = outfile)
      cat("<br><div class=\'image\'><img name=\'",
            paste(obj$refSet[i],"_heatmap",sep=""), "\' src=\'",
            paste(obj$refSet[i],"_heatmap.png",sep=""), "\'><br><br><caption>Fig 1: Heatmap: ",
            obj$refSet[i], "</caption></div><br>",sep="", file = outfile)
      cat("<br><div class=\'image\'><img name=\'",
            paste(obj$refSet[i],"_chrplot",sep=""), "\' src=\'",
            paste(obj$refSet[i],"_chrplot.png",sep=""), "\'><br><br><caption>Fig 2: Coherence plot: ",
            obj$refSet[i], "</caption></div><br>",sep="", file = outfile)
      cat("<div class=\'mytable\'> <table cols=\'7\' border=\'1\'>\n", file = outfile)
      cat(headings, file = outfile)
      cat("\n", file = outfile)
      cat(HTwrap(mainTable, tag = "TR"), file = outfile, sep = "\n")
      cat(paste("<caption class=\'table\'>Table: Gene sets significantly coherent by EEM&nbsp<a href=\'",paste(obj$refSet[i],".xls",sep=""),"\'>[plain text format]</a></caption>\n",sep=""),file = outfile)
      cat("</div>", "</table>", "<br>", "</body>", "</html>", sep = "\n", file = outfile)
      close(outfile)
    }

    # create gene set sizes
    status <- rep("",length(obj$nativeSetP))
    status[which(obj$setP<obj$minSetSize|obj$setP>obj$maxSetSize)] <- "Rejected!"
    restable <- cbind(obj$nativeRefSet,obj$nativeSetP,obj$setP,status)
    colnames(restable) <- c("NAME","ORIGINAL SIZE","AFTER RESTRICTING TO DATASET","STATUS")
    write.table(restable,paste(dirname,"/gene_set_sizes.xls",sep=""),col.names=TRUE,row.names=FALSE,sep="\t",quote=FALSE)

    # create report html and xls files
    restable <- cbind(obj$refSet,rep("Details...",length(obj$refSet)),lapply(obj$M,length),lapply(obj$C,length),obj$pvalue,obj$corrected.pvalue)
    restable <- cbind(1:nrow(restable),restable[sort.list(as.numeric(restable[,5]),decreasing=TRUE),,drop=FALSE])
    colnames(restable) <- c("","GS","GS Details","Size","Size of Coherent Subset","MinusLog10(p-value)","Corrected MinusLog10(p-value)")
    write.table(restable,paste(dirname,"/eem_report.xls",sep=""),col.names=TRUE,row.names=FALSE,sep="\t",quote=FALSE)
    mainTable <- cbind(HTwrap(restable[,1]),HTwrap(restable[,2]),HTwrap(HRwrap(restable[,3],paste(restable[,2],".html",sep=""))),
                                   HTwrap(restable[,4]),HTwrap(restable[,5]),HTwrap(round(as.numeric(restable[,6]),2)),HTwrap(round(as.numeric(restable[,7]),2)))
    mainTable <- apply(mainTable,1,paste,collapse="")
    headings <- paste(HTwrap(c("","GS","GS Details","Size","Size of Coherent Subset","-log10(p-value)","-log10(corrected p-value)"),class=rep("mytable",7),tag="TH"),collapse="")
    outfile <- file(paste(dirname,"/eem_report.html",sep=""), "w")
    cat("<html>\n", file = outfile)
    cat(HTwrap(paste(HTwrap("Report for EEM", tag = "TITLE"),"<link href=\'style.css\' rel=\'stylesheet\'>",sep=""), tag = "head"), "\n", file = outfile)
    cat("<body>\n",file = outfile)
    cat("<div class=\'mytable\'> <table cols=\'7\' border=\'1\'>\n", file = outfile)
    cat(headings, file = outfile)
    cat("\n", file = outfile)
    cat(HTwrap(mainTable, tag = "TR"), file = outfile, sep = "\n")
    cat("<caption class=\'table\'>Table: Gene sets significantly coherent by EEM&nbsp<a href=\'eem_report.xls\'>[plain text format]</a></caption>\n",file = outfile)
    cat("</div>", "</table>", "<br>", "</body>", "</html>", sep = "\n", file = outfile)
    close(outfile)

    # create index.html
    outfile <- file(paste(dirname,"/index.html",sep=""), "w")
    cat("<html>", file = outfile)
    cat(HTwrap(paste(HTwrap("Index for EEM", tag = "TITLE"),"<link href=\'style.css\' rel=\'stylesheet\'>",sep=""), tag = "head"), "\n", file = outfile)
    cat("<body>","<div id=\"footer\" style=\"width: 905; height: 35\">",
          "<h3 style=\"text-align: left\">",
          paste(paste("<font color=\"#808080\">EEM Report for Dataset",obj$dataName,sep=" "),"</font></h3></div>",sep=""),file = outfile, sep = "\n")
    cat("<div>",
          HTwrap("Coherence", tag = "h4"),
          "<ul>",
          HTwrap(paste(paste(sum(obj$pvalue>=-log10(0.01)),length(obj$refSet),sep=" / ")," gene sets are significantly coherent at p-value < 1 %",sep=""), tag = "li"),
          HTwrap(paste(paste(sum(obj$pvalue>=-log10(0.05)),length(obj$refSet),sep=" / ")," gene sets are significantly coherent at p-value < 5 %",sep=""), tag = "li"),
          HTwrap(paste("Detailed <a href=\'eem_report.html\'>coherent results in html</a> format"), tag="li"),
          HTwrap(paste("Detailed <a href=\'eem_report.xls\'>choherent results in excel</a> format (tab delimited text)"), tag="li"),
          HTwrap(paste("<a href=\'http://www.hgc.jp/~niiyan/REEM/\'>Guide</a> to interpret results"), tag="li"),
          "</ul>",
          "</div>", file = outfile, sep = "\n")
    cat("<div>",
          HTwrap("Dataset details", tag = "h4"),
          "<ul>",
          HTwrap(obj$messages[[1]], tag="li"),
          if(!is.null(obj$messages[[2]])) HTwrap(obj$messages[[2]], tag="li"),
          if(!is.null(obj$messages[[3]])) HTwrap(obj$messages[[3]], tag="li"),
          "</ul>",
          "</div>", file = outfile, sep = "\n")
    cat("<div>",
          HTwrap("Gene set details", tag = "h4"),
          "<ul>",
          if(!is.null(obj$messages[[4]])) HTwrap(obj$messages[[4]], tag="li"),
          if(!is.null(obj$messages[[5]])) HTwrap(obj$messages[[5]], tag="li"),
          HTwrap(paste("List of <a href=\'gene_set_sizes.xls\'>gene set used and their sizes</a>"), tag="li"),
          "</ul>",
          "</div>", file = outfile, sep = "\n")
    cat("<div>",
          HTwrap("Other", tag = "h4"),
          "<ul>",
          HTwrap("<a href=\'parameters.xls\'>Parameters</a> used for this analysis", tag="li"),
          "</ul>",
          "</div>", file = outfile, sep = "\n")
    cat("<br>","</body>","</html>",file = outfile, sep = "\n")
    close(outfile)

}

css <- "html, body {background-color: #FFFFFF;font-family: Tahoma, Helvetica, Arial, sans-serif;}
h1, h2, h3, h4, h5, h6 {color: #5A85D6;font-weight: bold;}
h1 {font-size: 20pt;text-align: left;}
h2 {font-size: 18pt;}
h3 {font-size: 16pt;}
h4 {font-size: 14pt;}
h5 {font-size: 12pt;}
h6 {font-size: 10pt;}
th.mytable {padding: 2px;border:2px solid;color: white;border-color: black; text-align: center;font-weight: bold;background-color: slategray}
caption.table {font-family: Tahoma, Helvetica, Arial, sans-serif;font-weight: bold;font-style: italic;padding: 10px;text-align: center;color: gray}
div.image {font-family: Tahoma, Helvetica, Arial, sans-serif;font-weight: bold;font-style: italic;border:2px solid;padding: 10px;text-align: center;/*background-color: #BBBBFF;*/}
th {padding: 0;}
td {text-align: left;padding: 3px;cell-spacing: 5px;border:1px solid;border-color: light gray;}
td.header {font-size: 10pt;padding: 0;}
div, h1, h2, h3, h4, h5, h6, em, b, li, ul, table, th, td {
font-family: Tahoma, Helvetica, Arial, sans-serif;}
em, b, li, ul, table, td {font-size: 10pt;}"

require("tcltk") # for tclvalue and tkgetOpenFile
require("Combinations") # for combinations
require("gplots") # for colorpanel and heatmap.2
